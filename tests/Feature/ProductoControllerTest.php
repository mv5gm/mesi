<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Existencia;
use App\Models\Moneda;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\User;
use App\Services\ImagenProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $basicRole;

    /**
     * Archivos públicos creados por las pruebas, para eliminarlos al finalizar.
     *
     * @var array<int, string>
     */
    private array $archivosPublicos = [];

    /**
     * Archivos temporales creados por las pruebas.
     *
     * @var array<int, string>
     */
    private array $archivosTemporales = [];

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar productos');
        $this->adminRole = Role::findOrCreate('admin');
        $this->adminRole->givePermissionTo('gestionar productos');
        $this->basicRole = Role::findOrCreate('usuario');
    }

    protected function tearDown(): void
    {
        foreach ($this->archivosPublicos as $ruta) {
            File::delete(public_path($ruta));
        }

        foreach ($this->archivosTemporales as $ruta) {
            File::delete($ruta);
        }

        parent::tearDown();
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();

        $user->assignRole($this->adminRole);

        return $user;
    }

    private function basicUser(): User
    {
        return tap(User::factory()->create(), fn (User $user) => $user->assignRole($this->basicRole));
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get('/productos')
            ->assertRedirect('/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAs($this->basicUser())
            ->get('/productos')
            ->assertForbidden();
    }

    public function test_admin_can_view_products_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/productos')
            ->assertOk()
            ->assertViewIs('productos.index')
            ->assertSee('Productos');
    }

    public function test_data_endpoint_lists_products(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Harina de Maíz']);

        $this->actingAs($this->adminUser())
            ->get('/productos/datos?draw=1')
            ->assertOk()
            ->assertJson([
                'draw' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ])
            ->assertJsonFragment(['sku' => $producto->sku]);
    }

    public function test_data_endpoint_includes_full_details_for_the_view_modal(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Filtros']);
        $moneda = Moneda::factory()->create(['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$']);

        Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'moneda_id' => $moneda->id,
            'nombre' => 'Filtro de Aceite',
            'descripcion' => 'Filtro para motor',
            'codigo_barras' => '7501234567890',
            'stock_minimo' => 3,
            'stock_maximo' => 50,
            'unidad_medida' => 'PZA',
        ]);

        $acciones = $this->actingAs($this->adminUser())
            ->get('/productos/datos?draw=1')
            ->assertOk()
            ->json('data.0.actions');

        $this->assertStringContainsString('openViewModal', $acciones);
        $this->assertStringContainsString('data-codigo-barras="7501234567890"', $acciones);
        $this->assertStringContainsString('data-descripcion="Filtro para motor"', $acciones);
        $this->assertStringContainsString('data-categoria-nombre="Filtros"', $acciones);
        $this->assertStringContainsString('data-stock="0"', $acciones);
    }

    public function test_el_listado_muestra_el_precio_en_dolares_y_bolivares(): void
    {
        $dolar = Moneda::factory()->create(['codigo' => 'USD', 'simbolo' => '$', 'es_moneda_base' => true]);
        $bolivar = Moneda::factory()->create(['codigo' => 'VES', 'simbolo' => 'Bs.', 'es_moneda_base' => false]);
        TasaCambio::factory()->create(['moneda_id' => $bolivar->id, 'tasa' => 950]);

        Producto::factory()->create(['moneda_id' => $dolar->id, 'precio_venta' => 10]);

        $this->actingAs($this->adminUser())
            ->get('/productos/datos?draw=1')
            ->assertOk()
            ->assertJsonFragment(['precio_venta' => '$ 10,00'])
            ->assertJsonFragment(['precio_venta_bolivares' => 'Bs. 9.500,00']);
    }

    public function test_valid_payload_creates_product(): void
    {
        $categoria = Categoria::factory()->create();
        $moneda = Moneda::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => 'PRD-001',
                'codigo_barras' => '7501000012345',
                'nombre' => 'Harina de Maíz',
                'descripcion' => 'Bolsa de 1kg',
                'categoria_id' => $categoria->id,
                'moneda_id' => $moneda->id,
                'precio_costo' => 2.50,
                'precio_venta' => 4.00,
                'stock_minimo' => 5,
                'stock_maximo' => 100,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto Harina de Maíz creado correctamente.'])
            ->assertJsonStructure(['message', 'producto_id']);

        $this->assertDatabaseHas('productos', [
            'sku' => 'PRD-001',
            'nombre' => 'Harina de Maíz',
            'stock_minimo' => 5,
        ]);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku', 'nombre', 'categoria_id', 'moneda_id', 'precio_costo', 'precio_venta', 'unidad_medida']);

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = Producto::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => $existing->sku,
                'nombre' => 'Otro producto',
                'categoria_id' => Categoria::factory()->create()->id,
                'moneda_id' => Moneda::factory()->create()->id,
                'precio_costo' => 1.00,
                'precio_venta' => 2.00,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);

        $this->assertDatabaseCount('productos', 1);
    }

    public function test_stock_maximum_cannot_be_lower_than_minimum(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', [
                'sku' => 'PRD-002',
                'nombre' => 'Producto de prueba',
                'categoria_id' => Categoria::factory()->create()->id,
                'moneda_id' => Moneda::factory()->create()->id,
                'precio_costo' => 1.00,
                'precio_venta' => 2.00,
                'stock_minimo' => 50,
                'stock_maximo' => 10,
                'unidad_medida' => 'PZA',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['stock_maximo']);
    }

    public function test_valid_payload_updates_product(): void
    {
        $producto = Producto::factory()->create(['nombre' => 'Nombre Antiguo']);
        $nuevaCategoria = Categoria::factory()->create();
        $moneda = Moneda::factory()->create();

        $this->actingAs($this->adminUser())
            ->put("/productos/{$producto->id}", [
                'sku' => $producto->sku,
                'nombre' => 'Nombre Nuevo',
                'categoria_id' => $nuevaCategoria->id,
                'moneda_id' => $moneda->id,
                'precio_costo' => 3.00,
                'precio_venta' => 5.50,
                'stock_minimo' => 2,
                'unidad_medida' => 'CAJA',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto actualizado correctamente.']);

        $fresh = $producto->fresh();

        $this->assertSame('Nombre Nuevo', $fresh->nombre);
        $this->assertSame(3.00, (float) $fresh->precio_costo);
        $this->assertSame($nuevaCategoria->id, $fresh->categoria_id);
    }

    public function test_product_with_existencias_cannot_be_deleted(): void
    {
        $producto = Producto::factory()->create();
        Existencia::factory()->create(['producto_id' => $producto->id]);

        $this->actingAs($this->adminUser())
            ->delete("/productos/{$producto->id}", headers: ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }

    public function test_empty_product_is_deleted(): void
    {
        $producto = Producto::factory()->create();

        $this->actingAs($this->adminUser())
            ->delete("/productos/{$producto->id}", headers: ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['message' => 'Producto eliminado correctamente.']);

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }

    public function test_product_can_be_created_with_an_image(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto([
                'imagen' => UploadedFile::fake()->image('foto.jpg', 900, 700),
            ]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto = Producto::where('sku', 'PRD-IMG')->firstOrFail();
        $this->archivosPublicos[] = $producto->imagen;

        $this->assertStringStartsWith(ImagenProductoService::CARPETA.'/', $producto->imagen);
        $this->assertFileExists(public_path($producto->imagen));
        $this->assertSame('image/jpeg', mime_content_type(public_path($producto->imagen)));
        $this->assertNotNull($producto->imagen_url);

        $listado = $this->actingAs($this->adminUser())->get('/productos/datos?draw=1');

        $listado->assertOk();
        $this->assertStringContainsString('producto-imagen', $listado->json('data.0.imagen'));
        $this->assertStringContainsString($producto->imagen, $listado->json('data.0.imagen'));
    }

    public function test_oversized_image_is_compressed_to_one_megabyte_or_less(): void
    {
        $rutaTemporal = $this->crearImagenPngPesada();
        $this->assertGreaterThan(ImagenProductoService::PESO_MAXIMO, filesize($rutaTemporal));

        $archivo = new UploadedFile($rutaTemporal, 'pesada.png', 'image/png', null, true);

        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto(['imagen' => $archivo]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto = Producto::where('sku', 'PRD-IMG')->firstOrFail();
        $this->archivosPublicos[] = $producto->imagen;
        $rutaGuardada = public_path($producto->imagen);

        clearstatcache(true, $rutaGuardada);

        $this->assertFileExists($rutaGuardada);
        $this->assertLessThanOrEqual(ImagenProductoService::PESO_MAXIMO, filesize($rutaGuardada));
        $this->assertSame('image/jpeg', mime_content_type($rutaGuardada));
    }

    public function test_non_image_file_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto([
                'imagen' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
            ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['imagen']);

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_updating_the_image_replaces_and_deletes_the_previous_file(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto([
                'imagen' => UploadedFile::fake()->image('foto.jpg', 400, 400),
            ]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto = Producto::where('sku', 'PRD-IMG')->firstOrFail();
        $rutaAnterior = $producto->imagen;
        $this->archivosPublicos[] = $rutaAnterior;

        $this->actingAs($this->adminUser())
            ->put("/productos/{$producto->id}", $this->datosProducto([
                'imagen' => UploadedFile::fake()->image('nueva.jpg', 500, 500),
            ]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto->refresh();
        $this->archivosPublicos[] = $producto->imagen;

        $this->assertNotSame($rutaAnterior, $producto->imagen);
        $this->assertFileDoesNotExist(public_path($rutaAnterior));
        $this->assertFileExists(public_path($producto->imagen));
    }

    public function test_image_can_be_removed_from_the_product(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto([
                'imagen' => UploadedFile::fake()->image('foto.jpg', 400, 400),
            ]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto = Producto::where('sku', 'PRD-IMG')->firstOrFail();
        $rutaAnterior = $producto->imagen;
        $this->archivosPublicos[] = $rutaAnterior;

        $this->actingAs($this->adminUser())
            ->put("/productos/{$producto->id}", $this->datosProducto(['quitar_imagen' => 1]), ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertNull($producto->fresh()->imagen);
        $this->assertFileDoesNotExist(public_path($rutaAnterior));
    }

    public function test_deleting_a_product_deletes_its_image(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/productos', $this->datosProducto([
                'imagen' => UploadedFile::fake()->image('foto.jpg', 400, 400),
            ]), ['Accept' => 'application/json'])
            ->assertOk();

        $producto = Producto::where('sku', 'PRD-IMG')->firstOrFail();
        $rutaImagen = $producto->imagen;
        $this->archivosPublicos[] = $rutaImagen;

        $this->actingAs($this->adminUser())
            ->delete("/productos/{$producto->id}", headers: ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertFileDoesNotExist(public_path($rutaImagen));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function datosProducto(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'PRD-IMG',
            'nombre' => 'Producto con imagen',
            'categoria_id' => Categoria::factory()->create()->id,
            'moneda_id' => Moneda::factory()->create()->id,
            'precio_costo' => 1.50,
            'precio_venta' => 3.00,
            'unidad_medida' => 'PZA',
        ], $overrides);
    }

    /**
     * Genera un PNG con ruido para superar el límite de 1 MB.
     */
    private function crearImagenPngPesada(): string
    {
        $lado = 1400;
        $imagen = imagecreatetruecolor($lado, $lado);

        for ($i = 0; $i < 12000; $i++) {
            $x = random_int(0, $lado - 16);
            $y = random_int(0, $lado - 16);
            $color = imagecolorallocate(
                $imagen,
                random_int(0, 255),
                random_int(0, 255),
                random_int(0, 255)
            );
            imagefilledrectangle($imagen, $x, $y, $x + 15, $y + 15, $color);
        }

        $ruta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'producto-pesada-'.uniqid().'.png';
        imagepng($imagen, $ruta, 0);
        imagedestroy($imagen);

        $this->archivosTemporales[] = $ruta;

        return $ruta;
    }
}
