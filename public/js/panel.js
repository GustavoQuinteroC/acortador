// Comportamiento del panel sin scripts en línea (la CSP solo permite archivos propios).
document.addEventListener('click', async (evento) => {
  const boton = evento.target.closest('[data-copiar]');
  if (!boton) return;
  const original = boton.innerHTML;
  try {
    await navigator.clipboard.writeText(boton.dataset.copiar);
    boton.innerHTML = '<i class="bi bi-check2"></i> Copiado';
  } catch {
    boton.textContent = 'No se pudo copiar';
  }
  setTimeout(() => { boton.innerHTML = original; }, 1500);
});

document.addEventListener('submit', (evento) => {
  const mensaje = evento.target.dataset.confirmar;
  if (mensaje && !window.confirm(mensaje)) evento.preventDefault();
});

const modalQr = document.getElementById('modal-qr');
modalQr?.addEventListener('show.bs.modal', (evento) => {
  const boton = evento.relatedTarget;
  document.getElementById('modal-qr-titulo').textContent = `QR de ${boton.dataset.qrTitulo}`;
  document.getElementById('modal-qr-imagen').src = boton.dataset.qr;
  document.getElementById('modal-qr-svg').href = `${boton.dataset.qr}?descargar=1`;
  document.getElementById('modal-qr-png').href = boton.dataset.qrPng;
});

document.querySelectorAll('canvas[data-grafica]').forEach((lienzo) => {
  if (typeof Chart === 'undefined') return;
  const { etiquetas, valores } = JSON.parse(lienzo.dataset.grafica);
  const fecha = new Intl.DateTimeFormat('es-MX', { day: 'numeric', month: 'short', timeZone: 'UTC' });
  new Chart(lienzo, {
    type: 'bar',
    data: {
      labels: etiquetas.map((d) => fecha.format(new Date(`${d}T00:00:00Z`))),
      datasets: [{ label: 'Visitas', data: valores, backgroundColor: '#2350ff', borderRadius: 4, maxBarThickness: 28 }],
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } } },
    },
  });
});
