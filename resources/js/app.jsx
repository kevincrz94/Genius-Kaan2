import React, {useEffect, useRef, useState} from 'react';
import ReactDOM from 'react-dom/client';
import {CognifitSdk} from '@cognifit/launcher-js-sdk';
import {
  CognifitSdkConfig,
} from '@cognifit/launcher-js-sdk/lib/lib/cognifit.sdk.config';

const DEFAULT_CLIENT_ID = '2cc41d68527b1b5eb49ee8ce8d802468';
const rootElement = document.getElementById ('app');

const statusCopy = {
  loading: {badge: 'Conectando', title: 'Preparando la sesion'},
  ready: {badge: 'En curso', title: 'Entrenamiento activo'},
  completed: {badge: 'Completado', title: 'Sesion finalizada'},
  error: {badge: 'Error', title: 'No fue posible iniciar'},
  missing: {badge: 'Pendiente', title: 'Faltan parametros'},
};

function readLaunchConfig () {
  if (!rootElement?.dataset.launchConfig) {
    return {
      participant: 'Paciente',
      goal: 'Entrenamiento cognitivo personalizado',
      gameKey: '',
      userToken: '',
      locale: 'es',
      clientId: DEFAULT_CLIENT_ID,
    };
  }

  try {
    const parsed = JSON.parse (rootElement.dataset.launchConfig);

    return {
      participant: parsed.participant || 'Paciente',
      goal: parsed.goal || 'Entrenamiento cognitivo personalizado',
      gameKey: parsed.gameKey || '',
      userToken: parsed.userToken || '',
      locale: parsed.locale || 'es',
      clientId: parsed.clientId || DEFAULT_CLIENT_ID,
    };
  } catch (error) {
    console.error ('Launch config parse error:', error);

    return {
      participant: 'Paciente',
      goal: 'Entrenamiento cognitivo personalizado',
      gameKey: '',
      userToken: '',
      locale: 'es',
      clientId: DEFAULT_CLIENT_ID,
    };
  }
}

const launchConfig = readLaunchConfig ();

function maskToken (token) {
  if (!token) return 'Sin token';
  if (token.length <= 12) return token;

  return `${token.slice (0, 6)}...${token.slice (-4)}`;
}

function App () {
  const containerRef = useRef (null);
  const [status, setStatus] = useState (
    launchConfig.userToken && launchConfig.gameKey ? 'loading' : 'missing'
  );
  const [message, setMessage] = useState (
    launchConfig.userToken && launchConfig.gameKey
      ? 'Conectando con Cognifit y preparando el entorno de juego.'
      : 'Completa user_token y game_key desde el configurador antes de iniciar.'
  );

  useEffect (() => {
    let sdkInstance;
    let gameSubscription;
    let disposed = false;

    const loadGame = async () => {
      if (!containerRef.current) return;

      if (!launchConfig.userToken || !launchConfig.gameKey) {
        setStatus ('missing');
        setMessage (
          'La sesion necesita un user_token valido y una clave de juego.'
        );
        return;
      }

      if (!launchConfig.clientId) {
        setStatus ('error');
        setMessage (
          'No se encontro COGNIFIT_CLIENT_ID. Define la variable y vuelve a intentar.'
        );
        return;
      }

      try {
        setStatus ('loading');
        setMessage (
          'Cargando el motor de entrenamiento y sincronizando la sesion.'
        );

        const config = new CognifitSdkConfig (
          containerRef.current.id,
          launchConfig.clientId,
          launchConfig.userToken,
          {
            sandbox: false,
            appType: 'web',
            theme: 'light',
            showResults: true,
            isFullscreenEnabled: true,
            scale: 100,
          }
        );

        sdkInstance = new CognifitSdk ();
        await sdkInstance.init (config);

        if (disposed) return;

        setStatus ('ready');
        setMessage (
          'La sesion esta activa. Mantener foco y evitar interrupciones mejora la lectura del progreso.'
        );

        gameSubscription = sdkInstance.start ('GAME', launchConfig.gameKey).subscribe ({
          next: response => {
            if (response.isEvent ()) {
              console.log ('Cognifit event:', response.eventPayload.getValues ());
            }
          },
          complete: () => {
            if (disposed) return;

            setStatus ('completed');
            setMessage (
              'La sesion termino. Puedes volver al panel y revisar los resultados registrados.'
            );
          },
          error: reason => {
            console.error ('Game error:', reason);

            if (disposed) return;

            setStatus ('error');
            setMessage (
              reason?.message ||
                'El juego no pudo iniciarse. Revisa token, client id y clave de juego.'
            );
          },
        });
      } catch (error) {
        console.error ('SDK initialization error:', error);

        if (disposed) return;

        setStatus ('error');
        setMessage (
          error?.message ||
            'La integracion no pudo conectarse con Cognifit. Revisa la configuracion.'
        );
      }
    };

    loadGame ();

    return () => {
      disposed = true;

      if (typeof gameSubscription?.unsubscribe === 'function') {
        gameSubscription.unsubscribe ();
      }

      if (typeof sdkInstance?.destroy === 'function') {
        sdkInstance.destroy ();
      }
    };
  }, []);

  const currentStatus = statusCopy[status] || statusCopy.loading;

  return (
    <div className="launcher-page">
      <style>{`
        :root {
          color-scheme: dark;
          --bg: #0f172a;
          --ink: #e2e8f0;
          --muted: #94a3b8;
          --line: rgba(148, 163, 184, 0.16);
          --panel: rgba(15, 23, 42, 0.78);
          --shadow: 0 30px 80px rgba(15, 23, 42, 0.45);
        }

        * { box-sizing: border-box; }

        body {
          margin: 0;
          font-family: 'Manrope', sans-serif;
          background:
            radial-gradient(circle at top left, rgba(251, 146, 60, 0.22), transparent 32%),
            radial-gradient(circle at right, rgba(14, 165, 233, 0.18), transparent 26%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
          color: var(--ink);
        }

        a {
          color: inherit;
          text-decoration: none;
        }

        .launcher-page {
          position: relative;
          min-height: 100vh;
          overflow: hidden;
          padding: 2rem;
        }

        .ambient {
          position: absolute;
          border-radius: 999px;
          filter: blur(40px);
          opacity: 0.55;
          pointer-events: none;
        }

        .ambient-left {
          width: 22rem;
          height: 22rem;
          top: -6rem;
          left: -7rem;
          background: rgba(249, 115, 22, 0.22);
        }

        .ambient-right {
          width: 18rem;
          height: 18rem;
          bottom: 2rem;
          right: -4rem;
          background: rgba(14, 165, 233, 0.18);
        }

        .launcher-header,
        .launcher-grid {
          position: relative;
          z-index: 1;
          width: min(1320px, 100%);
          margin: 0 auto;
        }

        .launcher-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 1rem;
          margin-bottom: 1.5rem;
        }

        .launcher-header h1 {
          margin: 0.4rem 0 0;
          font-family: 'Space Grotesk', sans-serif;
          font-size: clamp(2rem, 3.8vw, 3.4rem);
          line-height: 1.05;
          letter-spacing: -0.04em;
        }

        .launcher-header p {
          max-width: 42rem;
          margin: 0.85rem 0 0;
          color: var(--muted);
          line-height: 1.8;
        }

        .kicker {
          display: inline-flex;
          align-items: center;
          gap: 0.55rem;
          font-size: 0.82rem;
          font-weight: 800;
          letter-spacing: 0.12em;
          text-transform: uppercase;
          color: #fdba74;
        }

        .kicker::before {
          content: '';
          width: 0.7rem;
          height: 0.7rem;
          border-radius: 999px;
          background: linear-gradient(135deg, #fb923c, #f97316);
          box-shadow: 0 0 0 8px rgba(249, 115, 22, 0.12);
        }

        .status-pill {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          min-width: 8rem;
          padding: 0.85rem 1rem;
          border-radius: 999px;
          border: 1px solid var(--line);
          background: rgba(15, 23, 42, 0.62);
          font-size: 0.82rem;
          font-weight: 800;
          letter-spacing: 0.1em;
          text-transform: uppercase;
          backdrop-filter: blur(18px);
        }

        .status-ready,
        .status-completed {
          color: #86efac;
        }

        .status-loading {
          color: #fdba74;
        }

        .status-error,
        .status-missing {
          color: #fca5a5;
        }

        .launcher-grid {
          display: grid;
          grid-template-columns: 360px minmax(0, 1fr);
          gap: 1rem;
        }

        .info-panel,
        .experience-panel {
          border: 1px solid var(--line);
          border-radius: 28px;
          background: var(--panel);
          backdrop-filter: blur(18px);
          box-shadow: var(--shadow);
        }

        .info-panel {
          padding: 1.4rem;
        }

        .panel-block + .panel-block {
          margin-top: 1rem;
          padding-top: 1rem;
          border-top: 1px solid var(--line);
        }

        .panel-block h2,
        .experience-header h2 {
          margin: 0;
          font-family: 'Space Grotesk', sans-serif;
          font-size: 1.2rem;
          letter-spacing: -0.03em;
        }

        .meta-list {
          display: grid;
          gap: 0.85rem;
          margin-top: 1rem;
        }

        .meta-item {
          padding: 1rem;
          border-radius: 20px;
          background: rgba(15, 23, 42, 0.55);
          border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .meta-item span {
          display: block;
          color: var(--muted);
          font-size: 0.82rem;
          text-transform: uppercase;
          letter-spacing: 0.08em;
        }

        .meta-item strong {
          display: block;
          margin-top: 0.4rem;
          font-size: 1rem;
          word-break: break-word;
        }

        .tips {
          margin: 1rem 0 0;
          padding-left: 1.15rem;
          color: var(--muted);
          line-height: 1.8;
        }

        .tips li + li {
          margin-top: 0.5rem;
        }

        .action-row {
          display: flex;
          flex-wrap: wrap;
          gap: 0.75rem;
          margin-top: 1.25rem;
        }

        .text-link {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          min-height: 3rem;
          padding: 0 1rem;
          border-radius: 999px;
          border: 1px solid var(--line);
          background: rgba(255, 255, 255, 0.04);
          font-weight: 700;
        }

        .experience-panel {
          padding: 1rem;
        }

        .experience-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 1rem;
          margin-bottom: 1rem;
          padding: 0.6rem 0.35rem 0;
        }

        .experience-header p {
          max-width: 30rem;
          margin: 0;
          color: var(--muted);
          line-height: 1.8;
        }

        .experience-surface {
          position: relative;
          min-height: 70vh;
          border-radius: 24px;
          overflow: hidden;
          border: 1px solid rgba(148, 163, 184, 0.16);
          background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.1), rgba(15, 23, 42, 0.32)),
            rgba(255, 255, 255, 0.02);
        }

        .game-canvas {
          width: 100%;
          height: 70vh;
          background: rgba(15, 23, 42, 0.96);
        }

        .game-canvas.is-hidden {
          opacity: 0;
          pointer-events: none;
        }

        .overlay-card {
          position: absolute;
          inset: 1rem;
          z-index: 2;
          display: grid;
          place-items: center;
          padding: 2rem;
          text-align: center;
          border-radius: 20px;
          background: rgba(15, 23, 42, 0.8);
          border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .overlay-card strong {
          display: block;
          margin-bottom: 0.65rem;
          font-family: 'Space Grotesk', sans-serif;
          font-size: 1.4rem;
          letter-spacing: -0.03em;
        }

        .overlay-card p {
          max-width: 28rem;
          margin: 0;
          color: var(--muted);
          line-height: 1.8;
        }

        @media (max-width: 1024px) {
          .launcher-grid {
            grid-template-columns: 1fr;
          }

          .experience-header {
            flex-direction: column;
          }
        }

        @media (max-width: 720px) {
          .launcher-page {
            padding: 1rem;
          }

          .launcher-header {
            flex-direction: column;
          }

          .game-canvas,
          .experience-surface {
            min-height: 62vh;
            height: 62vh;
          }
        }
      `}</style>

      <div className="ambient ambient-left" />
      <div className="ambient ambient-right" />

      <header className="launcher-header">
        <div>
          <span className="kicker">Genius Kaan session</span>
          <h1>{launchConfig.participant}</h1>
          <p>{launchConfig.goal}</p>
        </div>

        <div className={`status-pill status-${status}`}>{currentStatus.badge}</div>
      </header>

      <main className="launcher-grid">
        <aside className="info-panel">
          <section className="panel-block">
            <h2>Contexto de la sesion</h2>
            <div className="meta-list">
              <div className="meta-item">
                <span>Juego</span>
                <strong>{launchConfig.gameKey || 'Pendiente'}</strong>
              </div>
              <div className="meta-item">
                <span>Token</span>
                <strong>{maskToken (launchConfig.userToken)}</strong>
              </div>
              <div className="meta-item">
                <span>Idioma</span>
                <strong>{launchConfig.locale.toUpperCase ()}</strong>
              </div>
            </div>
          </section>

          <section className="panel-block">
            <h2>Buenas practicas</h2>
            <ul className="tips">
              <li>Definir una meta puntual antes de iniciar mejora la lectura del progreso.</li>
              <li>Usar sesiones cortas y consistentes facilita adherencia y comparacion.</li>
              <li>Registrar resultados despues de cada bloque ayuda a ajustar dificultad.</li>
            </ul>
          </section>

          <div className="action-row">
            <a className="text-link" href="/launcher">
              Editar parametros
            </a>
            <a className="text-link" href="/admin/login">
              Ir al panel
            </a>
          </div>
        </aside>

        <section className="experience-panel">
          <div className="experience-header">
            <div>
              <span className="kicker">Estado actual</span>
              <h2>{currentStatus.title}</h2>
            </div>
            <p>{message}</p>
          </div>

          <div className="experience-surface">
            {status !== 'ready' && status !== 'completed' ? (
              <div className="overlay-card">
                <div>
                  <strong>{currentStatus.title}</strong>
                  <p>{message}</p>
                </div>
              </div>
            ) : null}

            <div
              id="cognifitContainer"
              ref={containerRef}
              className={`game-canvas ${
                status === 'missing' || status === 'error' ? 'is-hidden' : ''
              }`}
            />
          </div>
        </section>
      </main>
    </div>
  );
}

if (rootElement) {
  ReactDOM.createRoot (rootElement).render (<App />);
}
