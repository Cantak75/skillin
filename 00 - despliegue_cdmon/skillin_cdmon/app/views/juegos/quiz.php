<div class="topbar">
    <h1><?= htmlspecialchars($juego['titulo']) ?></h1>
    <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/juegos">← Volver</a>
</div>

<div class="card game-wrap">
    <div id="quiz-intro">
        <p><?= htmlspecialchars($juego['descripcion']) ?></p>
        <p style="font-size:13.5px; color:var(--texto-suave);">5 preguntas · cada acierto suma 20 puntos.</p>
        <button class="btn btn-primary" id="btn-empezar">Empezar quiz</button>
    </div>

    <div id="quiz-game" style="display:none;">
        <div class="progress-bar-bg" style="margin-bottom:18px;">
            <div class="progress-bar-fill" id="quiz-progress" style="width:0%;"></div>
        </div>
        <h3 id="quiz-pregunta"></h3>
        <div id="quiz-opciones"></div>
    </div>

    <div id="quiz-fin" style="display:none;">
        <h2>¡Quiz completado!</h2>
        <p style="font-size:40px; font-weight:800; color:var(--turquesa); margin:6px 0;"><span id="quiz-puntuacion">0</span> pts</p>
        <p id="quiz-resumen"></p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/dashboard">Volver al panel</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/juegos/jugar/<?= $asignacion['id_asignacion'] ?>">Reintentar</a>
    </div>
</div>

<script>
const PREGUNTAS = [
    {
        pregunta: "¿Qué debes hacer antes de utilizar una máquina nueva en tu puesto de trabajo?",
        opciones: ["Usarla directamente, ya la entenderás", "Leer el manual y/o pedir formación específica", "Preguntar a un compañero cualquiera", "Esperar a que falle para aprender"],
        correcta: 1
    },
    {
        pregunta: "Si detectas un riesgo de seguridad en tu área, ¿qué es lo primero que debes hacer?",
        opciones: ["Ignorarlo si no te afecta directamente", "Comunicarlo a tu responsable o al departamento de PRL", "Solucionarlo tú mismo sin avisar a nadie", "Esperar a que otro lo detecte"],
        correcta: 1
    },
    {
        pregunta: "¿Cuándo es obligatorio el uso de equipos de protección individual (EPI)?",
        opciones: ["Nunca, son opcionales", "Solo si hay inspección", "Siempre que el puesto lo requiera según la evaluación de riesgos", "Solo los días de más calor"],
        correcta: 2
    },
    {
        pregunta: "¿Qué actitud favorece más un buen clima de trabajo en equipo?",
        opciones: ["Comunicación clara y respeto mutuo", "Competir sin compartir información", "Evitar hablar con el resto del equipo", "Culpar siempre a otros de los errores"],
        correcta: 0
    },
    {
        pregunta: "Ante una situación de emergencia (incendio, evacuación...), debes:",
        opciones: ["Recoger tus objetos personales antes de salir", "Seguir el plan de evacuación y las indicaciones del responsable", "Usar el ascensor para salir más rápido", "Quedarte en tu puesto hasta confirmar que es real"],
        correcta: 1
    }
];

let indice = 0;
let puntuacion = 0;
let aciertos = 0;
const inicio = Date.now();

document.getElementById('btn-empezar').addEventListener('click', () => {
    document.getElementById('quiz-intro').style.display = 'none';
    document.getElementById('quiz-game').style.display = 'block';
    mostrarPregunta();
});

function mostrarPregunta() {
    const p = PREGUNTAS[indice];
    document.getElementById('quiz-progress').style.width = ((indice) / PREGUNTAS.length * 100) + '%';
    document.getElementById('quiz-pregunta').textContent = (indice + 1) + '. ' + p.pregunta;

    const cont = document.getElementById('quiz-opciones');
    cont.innerHTML = '';
    p.opciones.forEach((op, i) => {
        const btn = document.createElement('button');
        btn.className = 'quiz-option';
        btn.textContent = op;
        btn.onclick = () => seleccionar(i, btn);
        cont.appendChild(btn);
    });
}

function seleccionar(i, btnEl) {
    const p = PREGUNTAS[indice];
    const botones = document.querySelectorAll('.quiz-option');
    botones.forEach(b => b.onclick = null);

    if (i === p.correcta) {
        btnEl.classList.add('correct');
        puntuacion += 20;
        aciertos++;
    } else {
        btnEl.classList.add('incorrect');
        botones[p.correcta].classList.add('correct');
    }

    setTimeout(() => {
        indice++;
        if (indice < PREGUNTAS.length) {
            mostrarPregunta();
        } else {
            finalizar();
        }
    }, 800);
}

function finalizar() {
    document.getElementById('quiz-progress').style.width = '100%';
    document.getElementById('quiz-game').style.display = 'none';
    document.getElementById('quiz-fin').style.display = 'block';
    document.getElementById('quiz-puntuacion').textContent = puntuacion;
    document.getElementById('quiz-resumen').textContent = aciertos + ' de ' + PREGUNTAS.length + ' respuestas correctas.';

    const tiempo = Math.round((Date.now() - inicio) / 1000);
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
