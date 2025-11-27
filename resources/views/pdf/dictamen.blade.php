<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dictamen #{{ $reporte->id }}</title>
    <link rel="stylesheet" href="{{ public_path('pdf/dictamen.css') }}" type="text/css">
</head>

<body>
    <p class="text-right">Congreso del Estado de Veracruz de Ignacio de la llave</p>
    <p class="text-md text-right">Coordinación de Informática</p>

    <table>
        <tr class="text-middle">
            <td>
                <img src="{{ public_path('img/LOGO_LXVII_SLOGAN.jpg') }}" alt="Logo" style="height:120px">
            </td>
            <td class="text-center">
                <p class="text-lg">DICTAMEN TÉCNICO</p>
            </td>
            <td class="text-right">
                <div>
                    <strong>Folio C{{ $dictamen->id }}/{{ $dictamen->created_at->format('Y') }}</strong>
                </div>
                <div>
                    Xalapa, Ver. a {{ $dictamen->created_at->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>
    <p class="text-md" style="">Envío para su atención el siguiente dictamen técnico para que se le dé el trámite
        correspondiente.</p>

    <div class="cuerpo">
        <div class="row">
            <div class="col-1">Usuario:</div>
            <div class="col-2"><strong>{{ $reporte->solicitante }}</strong></div>
        </div>
        <div class="row">
            <div class="col-1">Área:</div>
            <div class="col-2"><strong>{{ $reporte->departamento->name ?? '-' }}</strong></div>
        </div></br>
        <div class="row">
            <div class="col-1" style="vertical-align: top;">Reporte:</div>
            <div class="col-2">
                <div class="row" style="vertical-align: top">
                    <div class="col-1" style="width: 16%">Help Desk #:</div>
                    <div class="col-2">{{ $reporte->id }}</div>
                </div>
                <div class="row">
                    <div class="col-2" style="width: 100%">
                        <table style="border:1px solid #818181;" >
                            <tr>
                                <td>Equipo</td>
                                <th colspan="3">{{ $dictamen->equipo }}</th>
                            </tr>
                            <tr>
                                <td>Marca</td>
                                <th class="text-left">{{ $dictamen->marca }}</th>
                                <td>Modelo</td>
                                <th class="text-left">{{ $dictamen->modelo }}</th>
                            </tr>
                            <tr>
                                <td>Serie</td>
                                <th class="text-left">{{ $dictamen->serie }}</th>
                                <td>Inventario</td>
                                <th class="text-left">{{ $dictamen->inventario }}</th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div></br>
        <div class="row">
            <div class="col-1">Diagnóstico</div>
            <div class="col-2">
                <p>{!! nl2br(e($dictamen->diagnostico)) !!}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-1">Sugerencia</div>
            <div class="col-2">
                <p>{!! nl2br(e($dictamen->sugerencia)) !!}</p>
            </div>
        </div>
        <div class="row">
            @if (!empty($dictamen->observaciones))
                <div class="col-1">Observaciones</div>
                <div class="col-2">
                    <p>{!! nl2br(e($dictamen->observaciones)) !!}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        <p class="text-md" style="">Sin otro particular, le envío un cordial saludo</p>
    </div>
    <div class="row" class="text-md">
        <div class="col-1 text-md" style="width:6%;"><strong>NOTA:</strong></div>
        <div class="col-2 text-md" style="width:93%;"><strong>ESTE DICTAMEN DEBERÁ ADJUNTARSE A SU SOLICITUD DIRIGIDA A
                LA DIRECCIÓN DE RECURSOS MATERIALES Y SERVICIOS GENERALES</strong></div>
    </div>
    <br><br><br><br><br>
    <table class="text-center">
        <tr>
            <td style="border-bottom:1px solid #000; padding-top:12px">&nbsp;</td>
            <td></td>
            <td style="border-bottom:1px solid #000; padding-top:12px">&nbsp;</td>
            <td></td>
            <td style="border-bottom:1px solid #000; padding-top:12px">&nbsp;</td>
        </tr>
        <tr style="height: 15px;">
            <td style="width:32%; line-height:1.05; padding:2px 4px;">{{ $reporte->tecnico->name }}</td>
            <td></td>
            <td style="width:32%; line-height:1.05; padding:2px 4px;">Ing. José Cruz Ruiz Miron</td>
            <td></td>
            <td style="width:32%; line-height:1.05; padding:2px 4px;">{{ $reporte->solicitante }}</td>
        </tr>
        <tr style="height: 15px;">
            <td style="width:32%; line-height:1; padding:1px 4px; font-size:0.9em;">Técnico</td>
            <td></td>
            <td style="width:32%; line-height:1; padding:1px 4px; font-size:0.9em;">Coordinador de informática</td>
            <td></td>
            <td style="width:32%; line-height:1; padding:1px 4px; font-size:0.9em;">Recibe de conformidad</td>
        </tr>
    </table>

</body>

</html>
