<div class="topbar">
    <h1><?= htmlspecialchars($juego['titulo']) ?></h1>
    <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/juegos">← Volver</a>
</div>

<div class="card game-wrap">
    <div id="mem-intro">
        <p><?= htmlspecialchars($juego['descripcion']) ?></p>
        <p style="font-size:13.5px; color:var(--texto-suave);">Encuentra las 8 parejas con el menor número de intentos posible.</p>
        <button class="btn btn-primary" id="btn-mem-empezar">Empezar</button>
    </div>

    <div id="mem-game" style="display:none;">
        <p style="font-size:13.5px;">Intentos: <strong id="mem-intentos">0</strong> · Parejas encontradas: <strong id="mem-parejas">0</strong>/8</p>
        <div class="memory-grid" id="memory-grid"></div>
    </div>

    <div id="mem-fin" style="display:none;">
        <h2>¡Completado!</h2>
        <p style="font-size:40px; font-weight:800; color:var(--turquesa); margin:6px 0;"><span id="mem-puntuacion">0</span> pts</p>
        <p id="mem-resumen"></p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/dashboard">Volver al panel</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/juegos/jugar/<?= $asignacion['id_asignacion'] ?>">Reintentar</a>
    </div>
</div>

<script>
const SIMBOLOS = ['📋','🛠️','🦺','⏱️','📦','🔑','📊','✅'];
let cartas = [];
let primeraCarta = null;
let bloqueo = false;
let intentos = 0;
let parejasEncontradas = 0;
let inicio;

document.getElementById('btn-mem-empezar').addEventListener('click', () => {
    document.getElementById('mem-intro').style.display = 'none';
    document.getElementById('mem-game').style.display = 'block';
    inicio = Date.now();
    iniciarTablero();
});

function iniciarTablero() {
    cartas = [...SIMBOLOS, ...SIMBOLOS]
        .map(s => ({ simbolo: s }))
        .sort(() => Math.random() - 0.5);

    const grid = document.getElementById('memory-grid');
    grid.innerHTML = '';

    cartas.forEach((c, idx) => {
        const div = document.createElement('div');
        div.className = 'memory-card';
        div.dataset.index = idx;
        div.textContent = '?';
        div.addEventListener('click', () => voltear(div, idx));
        grid.appendChild(div);
    });
}

function voltear(div, idx) {
    if (bloqueo || div.classList.contains('flipped') || div.classList.contains('matched')) return;

    div.classList.add('flipped');
    div.textContent = cartas[idx].simbolo;

    if (!primeraCarta) {
        primeraCarta = { div, idx };
        return;
    }

    intentos++;
    document.getElementById('mem-intentos').textContent = intentos;
    bloqueo = true;

    if (cartas[primeraCarta.idx].simbolo === cartas[idx].simbolo) {
        div.classList.add('matched');
        primeraCarta.div.classList.add('matched');
        parejasEncontradas++;
        document.getElementById('mem-parejas').textContent = parejasEncontradas;
        primeraCarta = null;
        bloqueo = false;

        if (parejasEncontradas === SIMBOLOS.length) {
            setTimeout(finalizar, 400);
        }
    } else {
        setTimeout(() => {
            div.classList.remove('flipped');
            div.textContent = '?';
            primeraCarta.div.classList.remove('flipped');
            primeraCarta.div.textContent = '?';
            primeraCarta = null;
            bloqueo = false;
        }, 700);
    }
}

function finalizar() {
    const tiempo = Math.round((Date.now() - inicio) / 1000);
    // Puntuación: base 100, penaliza intentos por encima del mínimo (8)
    const exceso = Math.max(0, intentos - SIMBOLOS.length);
    const puntuacion = Math.max(10, 100 - (exceso * 5));

    document.getElementById('mem-game').style.display = 'none';
    document.getElementById('mem-fin').style.display = 'block';
    document.getElementById('mem-puntuacion').textContent = puntuacion;
    document.getElementById('mem-resumen').textContent = intentos + ' intentos · ' + tiempo + ' segundos.';

    guardarResultado(puntuacion, tiempo);
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
