<!DOCTYPE html>
<html>
<head>
    <title>Constancia de Cumplimiento</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* --- ESTILOS ACTUALIZADOS --- */
        
        /* Define la fuente y el color de texto por defecto */
        body { 
            font-family: 'Helvetica', sans-serif; 
            margin: 50px; 
            color: #000000; /* Todo el texto negro */
            font-size: 11pt; /* Tamaño estándar de documento */
        }

        /* Contenedor del encabezado */
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            position: relative; /* Necesario para posicionar los logos */
            height: 100px; /* Alto fijo para alinear logos */
        }

        /* Logo UNAS (Izquierda) */
        .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 100px; /* Ajusta el tamaño si es necesario */
        }

        /* Logo FIIS (Derecha) */
        .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 100px; /* Ajusta el tamaño si es necesario */
        }

        /* Contenedor del texto del encabezado */
        .header-text {
            /* Márgenes para que el texto no se monte sobre los logos */
            margin-left: 110px; 
            margin-right: 110px;
        }

        .header-text h1 { 
            font-size: 16px; /* 16pt */
            margin: 10px 0 5px 0; 
            color: #000000; /* Negro */
            font-weight: bold;
        }
        .header-text h2 { 
            font-size: 14px; /* 14pt */
            margin: 0; 
            color: #000000; /* Negro */
            font-weight: bold;
        }
         .header-text h3 { 
            font-size: 15px; /* 15pt */
            margin-top: 25px;
            font-weight: bold;
            color: #000000; /* Negro */
            text-decoration: underline;
        }

        /* Cuerpo del documento */
        .content { 
            margin-top: 30px; 
            line-height: 1.8; /* Más espaciado para legibilidad */
            text-align: justify; 
            color: #000000; 
        }
        .content p { 
            margin-bottom: 15px; /* Párrafos más separados */
        }
        .highlight { 
            font-weight: bold; 
        }
        
        /* Pie de firma */
        .footer { 
            text-align: center; 
            margin-top: 100px; /* Más espacio para la firma */
            color: #000000; 
        }
        .signature-line { 
            border-top: 1px solid #000; 
            width: 50%; /* Ancho de la línea de firma */
            margin: 0 auto 5px auto; /* Centrada */
        }
        .signature-text { 
            text-align: center; 
            font-size: 12pt;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    
    <div class="header">
        <img src="{{ public_path('images/logo_unas.png') }}" class="logo-left" alt="Logo UNAS">
        
        <div class="header-text">
            <h1>UNIVERSIDAD NACIONAL AGRARIA DE LA SELVA</h1>
            <h2>FACULTAD DE INGENIERÍA EN INFORMÁTICA Y SISTEMAS</h2>
            <h3>CONSTANCIA DE CUMPLIMIENTO DE PRÁCTICAS PREPROFESIONALES</h3>
        </div>

        <img src="{{ public_path('images/logo_fiis_ppp.png') }}" class="logo-right" alt="Logo FIIS">
    </div>

    <div class="content">
        <p>El Director de la Escuela Profesional de Ingeniería en Informática y Sistemas de la Universidad Nacional Agraria de la Selva, que suscribe,</p>
        
        <p style="text-align: center; font-weight: bold; font-size: 14pt; margin-top: 25px; margin-bottom: 25px;">HACE CONSTAR:</p>
        
        <p>Que el/la estudiante <span class="highlight">{{ $practica->student->name }}</span> con código universitario <span class="highlight">{{ $practica->student->code ?? 'N/A' }}</span>, ha cumplido satisfactoriamente sus Prácticas Preprofesionales en la institución <span class="highlight">{{ $practica->entity_name }}</span>, desde el <span class="highlight">{{ \Carbon\Carbon::parse($practica->start_date)->format('d/m/Y') }}</span> hasta el <span class="highlight">{{ \Carbon\Carbon::parse($practica->end_date)->format('d/m/Y') }}</span>.</p>
        
        <p>Las prácticas realizadas fueron de tipo <span class="highlight">{{ $practica->practice_type }}</span> y han sido supervisadas por el/la Asesor(a) <span class="highlight">{{ $practica->advisor->name ?? 'No Asignado' }}</span>, obteniendo una calificación final de <span class="highlight">{{ $practica->final_grade }} / 20</span>.</p>
        
        <p>Se expide la presente a solicitud del/la interesado/a para los fines que estime conveniente, en Tingo María, a los {{ \Carbon\Carbon::parse($practica->constancia_emitted_at)->format('d') }} días del mes de {{ \Carbon\Carbon::parse($practica->constancia_emitted_at)->locale('es')->format('F') }} del {{ \Carbon\Carbon::parse($practica->constancia_emitted_at)->format('Y') }}.</p>
    </div>

    <div class="footer">
        
        
        
        @php
           
            $signature_file_path = null;
            if ($director && $director->signature_path) {
                // Esto crea la ruta: C:\Users\MANUEL\Music\ppp-unas-system\public\storage\signatures\KbVc...png
                $signature_file_path = public_path('storage/' . $director->signature_path);
            }
        @endphp

       
        @if ($signature_file_path && file_exists($signature_file_path))
            
            
            <div class="signature-image-container" style="text-align: center;">
                {{-- 3. Leemos el archivo y lo incrustamos en Base64 --}}
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signature_file_path)) }}" alt="Firma" style="width: 250px; height: auto; margin: 0 auto;">
            </div>
            
            <div class="signature-text" style="margin-top: -10px;"> 
                <strong>Dr. William Marchand Niño</strong><br>
                DIRECTOR DE LA ESCUELA PROFESIONAL<br>
                INGENIERÍA EN INFORMÁTICA Y SISTEMAS
            </div>

        @else

          
            <div class="signature-line"></div>
            <div class="signature-text">
                <strong>Dr. William Marchand Niño</strong><br>
                DIRECTOR DE LA ESCUELA PROFESIONAL<br>
                INGENIERÍA EN INFORMÁTICA Y SISTEMAS
            </div>

        @endif
        

    </div>

</body>
</html>