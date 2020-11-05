<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Prestamo_Herramienta;
use App\Prestamo_Herramienta_Detalle;
use App\Personal;
use Mail;
use Carbon\Carbon;
use Helpers;

class VerificarPrestamoHerramienta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:prestamo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los prestamos de herramienta que ya caducaron, si es asi se envia un correo electrónico de notificación';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $Prestamo_Herramienta = Prestamo_Herramienta::where('status', 'ALTA')->where('correo_enviado', NULL)->get();
        foreach($Prestamo_Herramienta as $ph){
            $fechaexactacomparacion = Carbon::now()->toDateTimeString();
            $fechaterminoprestamo = Carbon::parse($ph->termino_prestamo)->toDateTimeString();
            if($fechaexactacomparacion >= $fechaterminoprestamo){
                try {
                    //enviar correo electrónico	
                    $personal_entrega = Personal::where('id', $ph->entrega_herramienta)->first();
                    $personal_recibe = Personal::where('id', $ph->recibe_herramienta)->first();
                    $nombre = 'Receptor envio de correos';
                    $receptor = $ph->correo;
                    $correos = [$ph->correo];
                    $body = "El tiempo establecido en el prestamo de herramientas ya expiro, debes solicitar la herramienta que fue prestada a :".$personal_recibe->nombre;
                    $nombre_personal_entrega = $personal_entrega->nombre;
                    $nombre_personal_recibe = $personal_recibe->nombre;
                    $inicio_prestamo = Helpers::fecha_espanol_datetime($ph->inicio_prestamo);
                    $termino_prestamo = Helpers::fecha_espanol_datetime($ph->termino_prestamo);
                    $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
                    $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
                    $filascorreo = Prestamo_Herramienta_Detalle::where('prestamo', $ph->prestamo)->get();
                    Mail::send('correos.prestamos.prestamoterminado', compact('nombre_personal_entrega', 'nombre_personal_recibe', 'inicio_prestamo', 'termino_prestamo', 'body', 'receptor', 'horaaccionespanol','filascorreo'), function($message) use ($receptor, $correos) {
                        $message->to($receptor)
                                ->cc($correos)
                                ->subject('El tiempo establecido en prestamo de herramienta ya expiro');
                    });
                    //cambiar el campo correo_enviado
                    $Prestamo_Expiro = Prestamo_Herramienta::where('id', $ph->id)->first();
                    $Prestamo_Expiro->correo_enviado = 'ENVIADO';
                    $Prestamo_Expiro->save();
                } catch(\Exception $e) {
                    $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
                    $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
                    $msj = 'Error al enviar correo tiempo establecido en prestamo de herramienta ya expiro'.$ph->prestamo;
                    Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                        $message->to($receptor)
                                ->cc($correos)
                                ->subject('Error al enviar correo tiempo establecido en prestamo de herramienta ya expiro');
                    });
                }
            }else{
                echo "el tiempo establecido aun no expira";
            }
        }
    }
}
