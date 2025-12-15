<?php

namespace App\Http\Requests\CPPP;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AssignJuryRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo la CPPP puede hacer esto
        // Y solo si la práctica está esperando la asignación de jurado
        return Gate::allows('isCPPP') &&
               $this->route('practica')->status === 'pending_jury_assignment';
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        // Validamos que los 4 roles sean docentes válidos y distintos
        return [
            'presidente_id' => 'required|exists:users,id',
            
            'miembro1_id' => 'required|exists:users,id|different:presidente_id',
            
            'miembro2_id' => 'required|exists:users,id|different:presidente_id|different:miembro1_id',
            
            'suplente_id' => 'nullable|exists:users,id|different:presidente_id|different:miembro1_id|different:miembro2_id',
            
            'file_resolution' => 'required|file|mimes:pdf|max:2048', // Resolución de Jurado
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'presidente_id.required' => 'Debe seleccionar un Presidente.',
            'miembro1_id.required' => 'Debe seleccionar el Miembro 1.',
            'miembro2_id.required' => 'Debe seleccionar el Miembro 2.',
            'miembro1_id.different' => 'El Miembro 1 no puede ser el Presidente.',
            'miembro2_id.different' => 'El Miembro 2 no puede ser el Presidente ni el Miembro 1.',
            'suplente_id.different' => 'El Suplente no puede ser uno de los miembros principales.',
            'file_resolution.required' => 'Debe adjuntar la Resolución de Designación de Jurado.',
            'file_resolution.mimes' => 'La resolución debe ser un archivo PDF.',
        ];
    }

    /**
     * Almacena la asignación del jurado.
     * Asumo que estás usando Route-Model Binding por tu authorize()
     */
    public function storeJuryAssignment(AssignJuryRequest $request, PracticaPreprofesional $practica)
    {
        // 1. Lógica que YA TIENES (guardar archivo, etc.)
        // (ej. $path = $request->file('file_resolution')->store('resolutions');)
        // (ej. $practica->jurados()->create([...]);)

        // ...
        // ... (Aquí va todo tu código actual para guardar los datos)
        // ...


        // 2. NUEVO CÓDIGO: Enviar notificación
        
        // Recolectamos todos los IDs de los jurados del request
        $juryIds = [
            $request->presidente_id,
            $request->miembro1_id,
            $request->miembro2_id,
        ];
        
        // Añadimos al suplente SOLO si fue seleccionado
        if ($request->filled('suplente_id')) {
            $juryIds[] = $request->suplente_id;
        }

        // Obtenemos los modelos de Usuario para esos IDs
        $jurados = User::find($juryIds);

        // Enviamos la notificación a la colección de usuarios
        Notification::send($jurados, new JuradoAsignado($practica));
        
        // 3. Tu lógica de redirección
        return redirect()->back()->with('success', 'Jurado asignado y notificado exitosamente.');
    }
}