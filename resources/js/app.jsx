import './bootstrap';
import React, {useEffect, useRef, useState} from 'react';
import ReactDOM from 'react-dom/client';
import {CognifitSdk} from '@cognifit/launcher-js-sdk';
import {CognifitSdkConfig} from '@cognifit/launcher-js-sdk/lib/lib/cognifit.sdk.config';

const rootElement = document.getElementById('app');

const fallbackConfig = {
  participant: 'Elemento',
  goal: 'Evaluación cognitiva táctica',
  gameKey: '',
  userToken: '',
  locale: 'es',
  clientId: '',
};

const statusCopy = {
  loading: {badge: 'Conectando', title: 'Preparando simulador'},
  ready: {badge: 'En curso', title: 'Evaluación activa'},
  completed: {badge: 'Completado', title: 'Módulo finalizado'},
  error: {badge: 'Error', title: 'Falla de conexión'},
  missing: {badge: 'Pendiente', title: 'Parámetros incompletos'},
};

function readLaunchConfig() {
  if (!rootElement?.dataset.launchConfig) {
    return fallbackConfig;
  }

  try {
    const parsed = JSON.parse(rootElement.dataset.launchConfig);

    return {
      participant: parsed.participant || 'Elemento operativo',
      goal: parsed.goal || fallbackConfig.goal,
      gameKey: parsed.gameKey || '',
      userToken: parsed.userToken || '',
      locale: parsed.locale || 'es',
      clientId: parsed.clientId || '',
    };
  } catch (error) {
    console.error('Launch config parse error:', error);
    return fallbackConfig;
  }
}

const launchConfig = readLaunchConfig();

function maskToken(token) {
  if (!token) return 'Sin credencial';
  if (token.length <= 12) return token;

  return `${token.slice(0, 6)}...${token.slice(-4)}`;
}

function App() {
  const containerRef = useRef(null);
  const canLaunch = launchConfig.userToken && launchConfig.gameKey && launchConfig.clientId;
  const [status, setStatus] = useState(canLaunch ? 'loading' : 'missing');
  const [message, setMessage] = useState(
    canLaunch
      ? 'Conectando con CogniFit y asegurando el entorno de evaluación.'
      : 'Credenciales de acceso insuficientes. Solicite revisión al mando.'
  );

  useEffect(() => {
    let sdkInstance;
    let moduleSubscription;
    let disposed = false;

    const loadModule = async () => {
      if (!containerRef.current) return;

      if (!launchConfig.userToken || !launchConfig.gameKey) {
        setStatus('missing');
        setMessage('La sesión requiere credencial de elemento y clave de simulador válida.');
        return;
      }

      if (!launchConfig.clientId) {
        setStatus('error');
        setMessage('Error de configuración institucional: Client ID no detectado.');
        return;
      }

      try {
        setStatus('loading');
        setMessage('Cargando motor de evaluación y sincronizando la sesión.');

        const config = new CognifitSdkConfig(
          containerRef.current.id,
          launchConfig.clientId,
          launchConfig.userToken,
          {
            sandbox: false,
            appType: 'web',
            theme: 'dark',
            showResults: true,
            isFullscreenEnabled: true,
            scale: 100,
          }
        );

        sdkInstance = new CognifitSdk();
        await sdkInstance.init(config);

        if (disposed) return;

        setStatus('ready');
        setMessage('Sesión activa. Mantenga el foco visual y evite distracciones externas.');

        moduleSubscription = sdkInstance.start('GAME', launchConfig.gameKey).subscribe({
          next: response => {
            if (response.isEvent()) {
              console.log('CogniFit event:', response.eventPayload.getValues());
            }
          },
          complete: () => {
            if (disposed) return;
            setStatus('completed');
            setMessage('Evaluación finalizada. Los datos han sido enviados al panel de mando.');
          },
          error: reason => {
            console.error('Module error:', reason);
            if (disposed) return;
            setStatus('error');
            setMessage(reason?.message || 'El simulador fue interrumpido. Verifique su conexión.');
          },
        });
      } catch (error) {
        console.error('SDK initialization error:', error);
        if (disposed) return;
        setStatus('error');
        setMessage(error?.message || 'La plataforma no pudo enlazar con los servidores de evaluación.');
      }
    };

    loadModule();

    return () => {
      disposed = true;

      if (typeof moduleSubscription?.unsubscribe === 'function') {
        moduleSubscription.unsubscribe();
      }

      if (typeof sdkInstance?.destroy === 'function') {
        sdkInstance.destroy();
      }
    };
  }, []);

  const currentStatus = statusCopy[status] || statusCopy.loading;

  return (
    <div className="launcher-page">
      <style>{`
        :root {
          color-scheme: dark;
          --bg: #0b1120;
          --ink: #f8fafc;
          --muted: #94a3b8;
          --line: rgba(255, 255, 255, 0.1);
          --panel: rgba(15, 23, 42, 0.9);
          --shadow: 0 30px 80px rgba(0, 0, 0, 0.55);
          --accent-blue: #145da0;
          --accent-deep: #00254c;
          --accent-gold: #c7a34b;
        }

        * { box-sizing: border-box; }

        body {
          margin: 0;
          font-family: 'Manrope', sans-serif;
          background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
          color: var(--ink);
        }

        a { color: inherit; text-decoration: none; }

        .launcher-page {
          min-height: 100vh;
          padding: 2rem;
        }

        .launcher-header,
        .launcher-grid {
          width: min(1320px, 100%);
          margin: 0 auto;
        }

        .launcher-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 1rem;
          margin-bottom: 2rem;
        }

        .launcher-header h1 {
          margin: 0.4rem 0 0;
          font-family: 'Space Grotesk', sans-serif;
          font-size: clamp(1.8rem, 3.5vw, 3rem);
          line-height: 1.1;
        }

        .launcher-header p {
          max-width: 42rem;
          margin: 0.85rem 0 0;
          color: var(--muted);
          line-height: 1.6;
        }

        .kicker {
          display: inline-flex;
          align-items: center;
          gap: 0.55rem;
          font-size: 0.85rem;
          font-weight: 800;
          letter-spacing: 0.12em;
          text-transform: uppercase;
          color: var(--accent-gold);
        }

        .kicker::before {
          content: '';
          width: 0.7rem;
          height: 0.7rem;
          border-radius: 999px;
          background: var(--accent-gold);
          box-shadow: 0 0 0 6px rgba(199, 163, 75, 0.15);
        }

        .status-pill {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          min-width: 9rem;
          padding: 0.7rem 1.2rem;
          border-radius: 6px;
          border: 1px solid var(--line);
          background: rgba(15, 23, 42, 0.8);
          font-size: 0.85rem;
          font-weight: 700;
          letter-spacing: 0.08em;
          text-transform: uppercase;
        }

        .status-ready,
        .status-completed { border-left: 4px solid #10b981; }
        .status-loading { border-left: 4px solid var(--accent-gold); }
        .status-error,
        .status-missing { border-left: 4px solid #ef4444; }

        .launcher-grid {
          display: grid;
          grid-template-columns: 320px minmax(0, 1fr);
          gap: 1.5rem;
        }

        .info-panel,
        .experience-panel {
          border: 1px solid var(--line);
          border-radius: 12px;
          background: var(--panel);
          box-shadow: var(--shadow);
        }

        .info-panel { padding: 1.5rem; }

        .panel-block + .panel-block {
          margin-top: 1.5rem;
          padding-top: 1.5rem;
          border-top: 1px solid var(--line);
        }

        .panel-block h2,
        .experience-header h2 {
          margin: 0;
          font-family: 'Space Grotesk', sans-serif;
          font-size: 1.1rem;
          color: #fff;
        }

        .meta-list {
          display: grid;
          gap: 0.8rem;
          margin-top: 1rem;
        }

        .meta-item {
          padding: 0.8rem 1rem;
          border-radius: 8px;
          background: rgba(0, 0, 0, 0.2);
          border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .meta-item span {
          display: block;
          color: var(--muted);
          font-size: 0.75rem;
          text-transform: uppercase;
          letter-spacing: 0.08em;
        }

        .meta-item strong {
          display: block;
          margin-top: 0.2rem;
          font-size: 0.95rem;
          word-break: break-all;
        }

        .tips {
          margin: 1rem 0 0;
          padding-left: 1.2rem;
          color: var(--muted);
          line-height: 1.6;
          font-size: 0.9rem;
        }

        .tips li + li { margin-top: 0.6rem; }

        .action-row { margin-top: 2rem; }

        .text-link {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          min-height: 3rem;
          border-radius: 6px;
          border: 1px solid var(--line);
          background: rgba(255, 255, 255, 0.05);
          font-weight: 700;
          transition: background 0.2s ease;
        }

        .text-link:hover { background: rgba(255, 255, 255, 0.1); }

        .experience-panel { padding: 1.5rem; }

        .experience-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 1rem;
          margin-bottom: 1.5rem;
        }

        .experience-header p {
          max-width: 32rem;
          margin: 0;
          color: var(--muted);
          font-size: 0.95rem;
        }

        .experience-surface {
          position: relative;
          min-height: 65vh;
          border-radius: 8px;
          overflow: hidden;
          background: #000;
          border: 1px solid var(--line);
        }

        .game-canvas { width: 100%; height: 65vh; }
        .game-canvas.is-hidden { opacity: 0; pointer-events: none; }

        .overlay-card {
          position: absolute;
          inset: 0;
          z-index: 2;
          display: grid;
          place-items: center;
          padding: 2rem;
          text-align: center;
          background: rgba(11, 17, 32, 0.9);
        }

        .overlay-card strong {
          display: block;
          margin-bottom: 0.5rem;
          font-family: 'Space Grotesk', sans-serif;
          font-size: 1.3rem;
          color: #fff;
        }

        .overlay-card p {
          max-width: 26rem;
          margin: 0 auto;
          color: var(--muted);
        }

        @media (max-width: 1024px) {
          .launcher-grid { grid-template-columns: 1fr; }
          .experience-header { flex-direction: column; }
        }
      `}</style>

      <header className="launcher-header">
        <div>
          <span className="kicker">Sesión operativa</span>
          <h1>{launchConfig.participant}</h1>
          <p>{launchConfig.goal}</p>
        </div>
        <div className={`status-pill status-${status}`}>{currentStatus.badge}</div>
      </header>

      <main className="launcher-grid">
        <aside className="info-panel">
          <section className="panel-block">
            <h2>Parámetros técnicos</h2>
            <div className="meta-list">
              <div className="meta-item">
                <span>Simulador</span>
                <strong>{launchConfig.gameKey || 'Pendiente'}</strong>
              </div>
              <div className="meta-item">
                <span>Credencial</span>
                <strong>{maskToken(launchConfig.userToken)}</strong>
              </div>
              <div className="meta-item">
                <span>Idioma</span>
                <strong>{launchConfig.locale.toUpperCase()}</strong>
              </div>
            </div>
          </section>

          <section className="panel-block">
            <h2>Directrices operativas</h2>
            <ul className="tips">
              <li>Asegure un entorno libre de interrupciones para garantizar precisión en la métrica.</li>
              <li>Concéntrese exclusivamente en los estímulos visuales y auditivos en pantalla.</li>
              <li>Al finalizar, el puntaje se sincronizará con el panel de mando.</li>
            </ul>
          </section>

          <div className="action-row">
            <a className="text-link" href="/admin/dashboard">
              Cancelar y volver al panel
            </a>
          </div>
        </aside>

        <section className="experience-panel">
          <div className="experience-header">
            <div>
              <span className="kicker">Estado de sincronización</span>
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
              className={`game-canvas ${status === 'missing' || status === 'error' ? 'is-hidden' : ''}`}
            />
          </div>
        </section>
      </main>
    </div>
  );
}

if (rootElement) {
  ReactDOM.createRoot(rootElement).render(<App />);
}
