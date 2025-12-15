<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato F1: Ficha y Plan de Práctica</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; }
        .container { width: 95%; margin: 0 auto; }
        h1, h2, h3 { text-align: center; margin: 5px 0; }
        h1 { font-size: 16px; text-decoration: underline; }
        h2 { font-size: 14px; }
        h3 { font-size: 12px; font-weight: bold; }
        
        .logo { text-align: center; margin-bottom: 15px; }
        /* Logo de la UNAS (si está en public/logo.png) */
        /* .logo img { width: 100px; } */

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        table.info th {
            background-color: #eee;
            text-align: left;
            padding: 6px;
            border: 1px solid #000;
        }
        table.info td {
            padding: 5px 6px;
            vertical-align: top;
            border: 1px solid #000;
        }
        table.info td.label {
            font-weight: bold;
            width: 150px;
        }
        
        .section { margin-top: 20px; }
        .section-title { font-weight: bold; font-size: 12px; text-decoration: underline; margin-bottom: 8px; }
        .content { text-align: justify; white-space: pre-wrap; /* Respeta saltos de línea y espacios */ }

        .footer { text-align: center; margin-top: 40px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="logo">
        <h2>UNIVERSIDAD NACIONAL AGRARIA DE LA SELVA</h2>
        <h3>FACULTAD DE INGENIERÍA EN INFORMÁTICA Y SISTEMAS</h3>
        <h3>ESCUELA PROFESIONAL DE INGENIERÍA EN INFORMÁTICA Y SISTEMAS</h3>
    </div>

    <h2 style="text-decoration: underline; margin-top: 20px;">FORMATO F1</h2>
    <h3>FICHA DE PRÁCTICA PREPROFESIONAL</h3>

    <div class="container">
        
        <table class="info">
            <thead>
                <tr><th colspan="2">1. DATOS DE LA ENTIDAD</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">RAZÓN SOCIAL</td>
                    <td>{{ $practica->entity_name }}</td>
                </tr>
                <tr>
                    <td class="label">RUC</td>
                    <td>{{ $practica->entity_ruc }}</td>
                </tr>
                <tr>
                    <td class="label">TELÉFONO</td>
                    <td>{{ $practica->entity_phone }}</td>
                </tr>
                <tr>
                    <td class="label">DIRECCIÓN</td>
                    <td>{{ $practica->entity_address }}</td>
                </tr>
                <tr>
                    <td class="label">UBICACIÓN</td>
                    <td>{{ $practica->entity_district }} / {{ $practica->entity_province }} / {{ $practica->entity_department }}</td>
                </tr>
                <tr>
                    <td class="label">GERENTE/REPRESENTANTE</td>
                    <td>{{ $practica->entity_manager }}</td>
                </tr>
            </tbody>
        </table>

        <table class="info">
            <thead>
                <tr><th colspan="2">2. DE LA PRÁCTICA</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">FECHA DE INICIO</td>
                    <td>{{ \Carbon\Carbon::parse($practica->start_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">FECHA DE TÉRMINO</td>
                    <td>{{ \Carbon\Carbon::parse($practica->end_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">SUPERVISOR LABORAL</td>
                    <td>{{ $practica->supervisor_name }}</td>
                </tr>
                 <tr>
                    <td class="label">EMAIL SUPERVISOR</td>
                    <td>{{ $practica->supervisor_email }}</td>
                </tr>
            </tbody>
        </table>

        <table class="info">
            <thead>
                <tr><th colspan="2">3. DATOS DEL PRACTICANTE Y ASESOR ACADÉMICO</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">PRACTICANTE</td>
                    <td>{{ $practica->student->name }}</td>
                </tr>
                 <tr>
                    <td class="label">CÓDIGO</td>
                    <td>{{ $practica->student->code }}</td>
                </tr>
                 <tr>
                    <td class="label">EMAIL</td>
                    <td>{{ $practica->student->email }}</td>
                </tr>
                <tr>
                    <td class="label">ASESOR ACADÉMICO</td>
                    <td>{{ $practica->advisor->name }}</td>
                </tr>
                <tr>
                    <td class="label">EMAIL ASESOR</td>
                    <td>{{ $practica->advisor->email }}</td>
                </tr>
            </tbody>
        </table>

        <div class="page-break"></div>
        
        <div class="logo">
            <h2>UNIVERSIDAD NACIONAL AGRARIA DE LA SELVA</h2>
            <h3>FACULTAD DE INGENIERÍA EN INFORMÁTICA Y SISTEMAS</h3>
        </div>

        <h1 style="margin-top: 30px;">PLAN DE PRÁCTICA PREPROFESIONAL</h1>

        <table class="info" style="border: none; margin-top: 30px;">
            <tr style="border: none;">
                <td class="label" style="border: none;">TÍTULO</td>
                <td style="border: none;">: {{ $practica->title }}</td>
            </tr>
            <tr style="border: none;">
                <td class="label" style="border: none;">A REALIZARSE EN</td>
                <td style="border: none;">: {{ $practica->entity_name }}</td>
            </tr>
            <tr style="border: none;">
                <td class="label" style="border: none;">ÁREA</td>
                <td style="border: none;">: {{ $practica->practice_area }}</td>
            </tr>
            <tr style="border: none;">
                <td class="label" style="border: none;">PERIODO</td>
                <td style="border: none;">: Del {{ \Carbon\Carbon::parse($practica->start_date)->format('d/m/Y') }} 
                      al {{ \Carbon\Carbon::parse($practica->end_date)->format('d/m/Y') }}
                </td>
            </tr>
            <tr style="border: none;">
                <td class="label" style="border: none;">PRACTICANTE</td>
                <td style="border: none;">: {{ $practica->student->name }}</td>
            </tr>
            <tr style="border: none;">
                <td class="label" style="border: none;">ASESOR</td>
                <td style="border: none;">: {{ $practica->adisor->name }}</td>
            </tr>
        </table>

        <div class="section">
            <h3 class="section-title" style="text-decoration: none;">1. ASPECTOS GENERALES DE LA ENTIDAD</h3>
            <p class="content">{{ $practica->entity_details }}</p>
        </div>

        <div class="section">
            <h3 class="section-title" style="text-decoration: none;">2. OBJETIVOS DE LA PRÁCTICA</h3>
            <p class="content">{{ $practica->practice_objectives }}</p>
        </div>

        <div class="section">
            <h3 class="section-title" style="text-decoration: none;">3. ACTIVIDADES POR EJECUTARSE</h3>
            <p class="content">{{ $practica->practice_activities }}</p>
        </div>

        <div class="section">
            <h3 class="section-title" style="text-decoration: none;">4. CRONOGRAMA DE ACTIVIDADES</h3>
            <p class="content">{{ $practica->practice_schedule }}</p>
        </div>

        <div class="footer">
            <p>Tingo María – Perú</p>
            <p>{{ \Carbon\Carbon::now()->year }}</p>
        </div>
    </div>
</body>
</html>