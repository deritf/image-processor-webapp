document.addEventListener("DOMContentLoaded", () => {
  // Botones para abrir carpetas INPUT / OUTPUT
  document.querySelectorAll("[data-carpeta]").forEach((boton) => {
    boton.addEventListener("click", () => {
      const nombreCarpeta = boton.dataset.carpeta;
      abrirCarpeta(nombreCarpeta);
    });
  });

  // Botón para limpiar (borrar todos los archivos) carpeta OUTPUT
  const btnLimpiar = document.getElementById("limpiar-output");
  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", () => {
      if (
        confirm(
          "¿Seguro que deseas eliminar todos los archivos de la carpeta OUTPUT?"
        )
      ) {
        fetch("limpiar-output.php", { method: "POST" })
          .then((res) => {
            if (!res.ok) throw new Error("Fallo al limpiar carpeta");
            return res.json();
          })
          .then(() => location.reload())
          .catch((err) => {
            console.error("Error limpiando OUTPUT:", err);
            alert("Ocurrió un error al limpiar la carpeta OUTPUT.");
          });
      }
    });
  }

  // Mostrar/Ocultar opciones avanzadas
  const toggleBtn = document.getElementById("toggle-edicion");
  const panel = document.getElementById("edicion-avanzada");

  if (toggleBtn && panel) {
    toggleBtn.addEventListener("click", () => {
      const visible = panel.style.display === "block";

      panel.style.display = visible ? "none" : "block";

      toggleBtn.textContent = visible
        ? "Mostrar opciones avanzadas"
        : "Ocultar opciones avanzadas";

      toggleBtn.setAttribute("aria-expanded", String(!visible));
    });
  }

  // Actualizar tamaños al iniciar y cada 10 segundos
  actualizarTamanos();
  setInterval(actualizarTamanos, 10000);
});

// Función para abrir carpeta desde PHP
function abrirCarpeta(nombreCarpeta) {
  if (!["INPUT", "OUTPUT"].includes(nombreCarpeta)) return;

  fetch(`abrir-carpeta.php?carpeta=${encodeURIComponent(nombreCarpeta)}`)
    .then((response) => {
      if (!response.ok) throw new Error("No se pudo abrir la carpeta");
    })
    .catch((error) => {
      console.error("Error al abrir carpeta:", error);
      alert(`Error al abrir la carpeta ${nombreCarpeta}.`);
    });
}

// Función para actualizar tamaños y fechas
function actualizarTamanos() {
  fetch("tamanos.php")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("tamano-input").textContent = data.input.tamano;
      document.getElementById("archivos-input").textContent =
        data.input.cantidad;
      document.getElementById("modificacion-input").textContent =
        data.input.ultima;

      document.getElementById("tamano-output").textContent = data.output.tamano;
      document.getElementById("archivos-output").textContent =
        data.output.cantidad;
      document.getElementById("modificacion-output").textContent =
        data.output.ultima;
    })
    .catch((err) => {
      console.error("Error al obtener tamaños:", err);
    });
}
