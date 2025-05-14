use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscripcionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion'); // Primary key
            $table->unsignedBigInteger('id_torneos'); // Foreign key to torneos
            $table->unsignedBigInteger('id_equipo'); // Foreign key to equipos
            $table->date('fecha_inscripcion'); // Date of inscription

            // Foreign key constraints
            $table->foreign('id_torneos')->references('id_torneos')->on('torneos')->onDelete('cascade');
            $table->foreign('id_equipo')->references('id_equipos')->on('equipos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('inscripcion');
    }
}
