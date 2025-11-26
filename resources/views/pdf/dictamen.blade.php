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
            <div class="col-2 text-lg">{{ $reporte->solicitante }}</div>
        </div>
        <div class="row">
            <div class="col-1">Área:</div>
            <div class="col-2 text-lg">{{ $reporte->departamento->name ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col-1" style="vertical-align: top;">Reporte:</div>
            <div class="col-2">
                <div class="row">
                    <div class="col-1">Número:</div>
                    <div class="col-2">{{ $reporte->id }}</div>
                </div>
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-2">
                        <table>
                            <tr>
                                <th>Inventario</th>
                                <td>{{ $dictamen->inventario }}</td>
                                <th>Equipo</th>
                                <td>{{ $dictamen->equipo }}</td>
                            </tr>
                            <tr>
                                <th>Marca</th>
                                <td>{{ $dictamen->marca }}</td>
                                <th>Modelo</th>
                                <td>{{ $dictamen->modelo }}</td>
                            </tr>
                            <tr>
                                <th>Serie</th>
                                <td colspan="3">{{ $dictamen->serie }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- <h2>Datos del equipo</h2>
        <table>
            <tr>
                <th>Inventario</th>
                <td>{{ $dictamen->inventario }}</td>
                <th>Equipo</th>
                <td>{{ $dictamen->equipo }}</td>
            </tr>
            <tr>
                <th>Marca</th>
                <td>{{ $dictamen->marca }}</td>
                <th>Modelo</th>
                <td>{{ $dictamen->modelo }}</td>
            </tr>
            <tr>
                <th>Serie</th>
                <td colspan="3">{{ $dictamen->serie }}</td>
            </tr>
        </table> --}}

        <div class="row">
            <div class="col-1">Diagnóstico</div>
            <div class="col-2"><p>{!! nl2br(e($dictamen->diagnostico)) !!}</p></div>
        </div>
        <div class="row">
            <div class="col-1">Sugerencia</div>
            <div class="col-2"><p>{!! nl2br(e($dictamen->sugerencia)) !!}</p></div>
        </div>
        <div class="row">
            @if (!empty($dictamen->observaciones))
            <div class="col-1">Observaciones</div>
            <div class="col-2"><p>{!! nl2br(e($dictamen->observaciones)) !!}</p></div>
            @endif
        </div>


        {{-- <h2>Información del reporte</h2>
        <table>
            <tr>
                <th>Área solicitante</th>
                <td>{{ $reporte->departamento->name ?? '-' }}</td>
                <th>Solicitante</th>
                <td>{{ $reporte->solicitante ?? '-' }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ $reporte->estado->name ?? '-' }}</td>
                <th>Categoría</th>
                <td>{{ $reporte->categoria->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Técnico principal</th>
                <td colspan="3">{{ $reporte->tecnico->name ?? '-' }}</td>
            </tr>
        </table> --}}
    </div>
</body>

</html>
