<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function home()
    {
        $pageTitle = 'Genius Kaan | Desarrollo cognitivo';

        $signals = [
            [
                'label' => 'Rutinas adaptativas',
                'value' => '12 min',
                'description' => 'Bloques cortos para sostener energia, foco y adherencia.',
            ],
            [
                'label' => 'Objetivos activos',
                'value' => '5 areas',
                'description' => 'Memoria, atencion, velocidad, razonamiento y flexibilidad.',
            ],
            [
                'label' => 'Seguimiento',
                'value' => '360deg',
                'description' => 'Vision compartida para familias, terapeutas y coordinadores.',
            ],
            [
                'label' => 'Entrenamiento',
                'value' => '1 panel',
                'description' => 'Evaluacion, sesiones y reportes conectados en un mismo flujo.',
            ],
        ];

        $pillars = [
            [
                'title' => 'Atencion ejecutiva',
                'description' => 'Rutinas para sostener foco, alternar tareas y reducir la fatiga cognitiva.',
                'accent' => '#ef8354',
            ],
            [
                'title' => 'Memoria funcional',
                'description' => 'Ejercicios para retener, manipular y recuperar informacion util en contexto.',
                'accent' => '#2a9d8f',
            ],
            [
                'title' => 'Lenguaje y comprension',
                'description' => 'Actividades enfocadas en expresion, procesamiento verbal y respuesta rapida.',
                'accent' => '#ffb703',
            ],
            [
                'title' => 'Razonamiento',
                'description' => 'Desafios para reconocer patrones, anticipar soluciones y tomar decisiones.',
                'accent' => '#577590',
            ],
            [
                'title' => 'Autorregulacion',
                'description' => 'Micro rutinas para bajar saturacion, ordenar tiempos y mejorar constancia.',
                'accent' => '#6d597a',
            ],
            [
                'title' => 'Velocidad de procesamiento',
                'description' => 'Estimulos medibles para acelerar lectura de senales y tiempos de respuesta.',
                'accent' => '#bc4749',
            ],
        ];

        $journey = [
            [
                'step' => '01',
                'title' => 'Evaluar el punto de partida',
                'description' => 'Capturamos fortalezas, alertas y metas para no entrenar a ciegas.',
            ],
            [
                'step' => '02',
                'title' => 'Disenar sesiones utiles',
                'description' => 'Cada rutina responde a una necesidad concreta y a un perfil cognitivo real.',
            ],
            [
                'step' => '03',
                'title' => 'Seguir resultados',
                'description' => 'El progreso se revisa por habilidad, adherencia y consistencia en el tiempo.',
            ],
        ];

        $audiences = [
            [
                'title' => 'Infancia',
                'description' => 'Estimulos ludicos para lenguaje, memoria de trabajo y control inhibitorio.',
            ],
            [
                'title' => 'Adolescencia',
                'description' => 'Rutinas para organizacion, foco sostenido y rendimiento academico.',
            ],
            [
                'title' => 'Adultos',
                'description' => 'Programas de productividad mental, flexibilidad y toma de decisiones.',
            ],
            [
                'title' => 'Adulto mayor',
                'description' => 'Planes orientados a mantenimiento cognitivo, autonomia y bienestar diario.',
            ],
        ];

        return view('welcome', compact('pageTitle', 'signals', 'pillars', 'journey', 'audiences'));
    }

    public function launcher(Request $request)
    {
        $pageTitle = 'Genius Kaan | Preparar sesion';

        $availableGames = [
            [
                'key' => 'THE_BLUE_SHAPE',
                'title' => 'The Blue Shape',
                'focus' => 'Atencion selectiva y velocidad de respuesta.',
            ],
            [
                'key' => 'MAHJONG',
                'title' => 'Mahjong',
                'focus' => 'Memoria visual, estrategia y reconocimiento de patrones.',
            ],
            [
                'key' => 'PIT_STOP',
                'title' => 'Pit Stop',
                'focus' => 'Planificacion, alternancia mental y control ejecutivo.',
            ],
            [
                'key' => 'FROGGY_CROSSING',
                'title' => 'Froggy Crossing',
                'focus' => 'Coordinacion, anticipacion y gestion de impulsos.',
            ],
        ];

        $sessionDefaults = [
            'participant' => $request->string('participant')->trim()->value() ?: 'Paciente demo',
            'goal' => $request->string('goal')->trim()->value() ?: 'Fortalecer atencion y memoria funcional',
            'user_token' => $request->string('user_token')->trim()->value(),
            'game_key' => strtoupper($request->string('game_key')->trim()->value() ?: 'THE_BLUE_SHAPE'),
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
        ];

        return view('launcher', compact('pageTitle', 'availableGames', 'sessionDefaults'));
    }

    public function startGame(Request $request)
    {
        $pageTitle = 'Genius Kaan | Sesion cognitiva';

        $launchConfig = [
            'participant' => $request->string('participant')->trim()->value() ?: 'Paciente',
            'goal' => $request->string('goal')->trim()->value() ?: 'Entrenamiento cognitivo personalizado',
            'gameKey' => strtoupper($request->string('game_key')->trim()->value()),
            'userToken' => $request->string('user_token')->trim()->value(),
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
            'clientId' => config('services.cognifit.client_id') ?: '2cc41d68527b1b5eb49ee8ce8d802468',
        ];

        return view('index', compact('pageTitle', 'launchConfig'));
    }
}
