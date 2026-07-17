<div class="topbar">
    <h1><?= htmlspecialchars($juego['titulo']) ?></h1>
    <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/juegos">← Volver</a>
</div>

<div class="card game-wrap">
    <div id="rx-intro">
        <p><?= htmlspecialchars($juego['descripcion']) ?></p>
        <p style="font-size:13.5px; color:var(--texto-suave);">Se realizarán 5 rondas. Haz clic en la caja en cuanto se ponga VERDE. ¡Cuidado con hacer clic antes de tiempo!</p>
        <button class="btn btn-primary" id="btn-rx-empezar">Empezar</button>
    </div>

    <div id="rx-game" style="display:none;">
        <p style="font-size:13.5px;">Ronda <strong id="rx-ronda">1</strong>/5</p>
        <div class="reaction-box wait" id="rx-box">Espera…</div>
        <p id="rx-mensaje" style="min-height:20px; margin-top:10px;"></p>
    </div>

    <div id="rx-fin" style="display:none;">
        <h2>¡Completado!</h2>
        <p style="font-size:40px; font-weight:800; color:var(--turquesa); margin:6px 0;"><span id="rx-puntuacion">0</span> pts</p>
        <p id="rx-resumen"></p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/dashboard">Volver al panel</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/juegos/jugar/<?= $asignacion['id_asignacion'] ?>">Reintentar</a>
    </div>
</div>

<script>
const TOTAL_RONDAS = 5;
let ronda = 0;
let tiempos = [];
let horaCambio = 0;
let esperandoClick = false;
let timeoutId;
const inicioTotal = Date.now();

const box = document.getElementById('rx-box');
const mensaje = document.getElementById('rx-mensaje');

document.getElementById('btn-rx-empezar').addEventListener('click', () => {
    document.getElementById('rx-intro').style.display = 'none';
    document.getElementById('rx-game').style.display = 'block';
    siguienteRonda();
});

box.addEventListener('click', () => {
    if (esperandoClick) {
        const reaccion = Date.now() - horaCambio;
        tiempos.push(reaccion);
        esperandoClick = false;
        mensaje.textContent = 'Tiempo de reacción: ' + reaccion + ' ms';
        box.classList.remove('go');
        box.classList.add('wait');
        box.textContent = 'Espera…';

        ronda++;
        if (ronda < TOTAL_RONDAS) {
            setTimeout(siguienteRonda, 900);
        } else {
            setTimeout(finalizar, 700);
        }
    } else if (timeoutId) {
        // clic anticipado -> penalización
        clearTimeout(timeoutId);
        mensaje.textContent = '¡Demasiado pronto! Penalización aplicada.';
        tiempos.push(1200); // tiempo "malo" fijo de penalización
        box.textContent = 'Espera…';

        ronda++;
        if (ronda < TOTAL_RONDAS) {
            setTimeout(siguienteRonda, 900);
        } else {
            setTimeout(finalizar, 700);
        }
    }
});

function siguienteRonda() {
    document.getElementById('rx-ronda').textContent = (ronda + 1);
    box.classList.remove('go');
    box.classList.add('wait');
    box.textContent = 'Espera…';
    mensaje.textContent = '';
    esperandoClick = false;

    const espera = 1200 + Math.random() * 2200; // entre 1.2s y 3.4s
    timeoutId = setTimeout(() => {
        box.classList.remove('wait');
        box.classList.add('go');
        box.textContent = '¡AHORA!';
        horaCambio = Date.now();
        esperandoClick = true;
        timeoutId = null;
    }, espera);
}

function finalizar() {
    const media = Math.round(tiempos.reduce((a, b) => a + b, 0) / tiempos.length);
    // Puntuación: cuanto menor el tiempo medio de reacción, mayor la puntuación (máx 100)
    const puntuacion = Math.max(10, Math.min(100, Math.round(100 - (media - 200) / 10)));
    const tiempoTotal = Math.round((Date.now() - inicioTotal) / 1000);

    document.getElementById('rx-game').style.display = 'none';
    document.getElementById('rx-fin').style.display = 'block';
    document.getElementById('rx-puntuacion').textContent = puntuacion;
    document.getElementById('rx-resumen').textContent = 'Tiempo medio de reacción: ' + media + ' ms.';

    guardarResultado(puntuacion, tiempoTotal);
}

function guardarResultado(puntuacion, tiempo) {
    fetch('<?= BASE_URL ?>/juegos/resultado', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            csrf_token: '<?= $csrf ?>',
            id_juego: '<?= $juego['id_juego'] ?>',
            id_asignacion: '<?= $asignacion['id_asignacion'] ?>',
            puntuacion: puntuacion,
            tiempo: tiempo
        })
    });
}
</script>
