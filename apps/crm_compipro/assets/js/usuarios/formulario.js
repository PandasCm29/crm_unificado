import { switchModal, vaciarForm } from "../utils.js";

document.addEventListener("DOMContentLoaded", () => {
  cargarAreas();
  const fields = [
    {
      input: document.getElementById("dni"),
      validate: (value) => /^\d{8}$/.test(value),
      emptyMessage: "Formato: 8 dígitos numéricos",
      validMessage: "✅ DNI válido",
      invalidMessage: "❌ DNI inválido. Debe tener 8 dígitos",
    },
    {
      input: document.getElementById("celular"),
      validate: (value) => /^9\d{8}$/.test(value), // E.g., 923612546
      emptyMessage: "Formato: 9 dígitos empezando con 9",
      validMessage: "✅ Número válido",
      invalidMessage: "❌ Formato inválido. Ej: 923612546",
    },
    {
      input: document.getElementById("correo"),
      validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
      emptyMessage: "Formato: correo@dominio.com",
      validMessage: "✅ Correo válido",
      invalidMessage: "❌ Correo inválido. Ej: correo@dominio.com",
    },
  ];

  const updateFieldStyle = (
    input,
    messageElement,
    value,
    { validate, emptyMessage, validMessage, invalidMessage }
  ) => {
    input.classList.remove(
      "border-green-500",
      "border-red-500",
      "focus:ring-green-500",
      "focus:ring-red-500"
    );
    messageElement.classList.remove("text-green-600", "text-red-600");
    messageElement.classList.add("text-gray-500");

    if (value === "") {
      messageElement.textContent = emptyMessage;
      return;
    }

    input.classList.remove("border-gray-300");
    if (validate(value)) {
      messageElement.classList.remove("text-gray-500", "text-red-600");
      messageElement.classList.add("text-green-600");
      messageElement.textContent = validMessage;
    } else {
      messageElement.classList.remove("text-gray-500", "text-green-600");
      messageElement.classList.add("text-red-600");
      messageElement.textContent = invalidMessage;
    }
  };

  fields.forEach((field) => {
    field.input.addEventListener("input", () => {
      updateFieldStyle(
        field.input,
        field.input.nextElementSibling,
        field.input.value,
        field
      );
    });
  });
  function vaciarFormUsuario(form) {
    fields.forEach(({ input }) => {
      const messageElement = input.nextElementSibling;
      messageElement.textContent = ""; // Reset to empty message
    });
    vaciarForm(form);
  }
  // MODAL EDITAR
  const modalEditar = document.getElementById("modal-editar-usuario");
  const button = document.querySelector(".submit-btn");
  const spinner = button.querySelector('.spinner');
  const btnCerrar = modalEditar?.querySelector("#btn-cerrar-modal");
  const formUsuario = document.getElementById("form-usuario");
  const btnSearch = document.getElementById("button-search-users");
  btnCerrar?.addEventListener("click", () => switchModal(modalEditar));
  // FORMULARIO
  formUsuario?.addEventListener("submit", async (e) => {
    e.preventDefault();
    let isValidForm = true;

    fields.forEach((field) => {
      const isValid = field.validate(field.input.value);
      updateFieldStyle(
        field.input,
        field.input.nextElementSibling,
        field.input.value,
        field
      );
      if (!isValid && field.input.value !== "") isValidForm = false;
    });

    if (!isValidForm) {
      return;
    }
    const form = e.target;
    const formData = new FormData(form);
    const formMode = form.dataset.mode;
    const esEditar = formMode === "Editado";
    const method = esEditar ? "PUT" : "POST";
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    if (esEditar) {
      data.idusuario = idusuario;
    }
    // for (const [key, value] of Object.entries(data)) {
    //   console.log(`${key}: ${value}`);
    // }
    try {
      if(button) button.disabled = true;
      switchModal(spinner, true);
      const response = await fetch(
        // base + "controller/usuarios/Notion/sync_to_notion_usuario.php",
        base + "controller/usuarios/guardar.php",
        {
          method: method,
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
        }
      );

      const text = await response.text();
      console.log(text);
      // Verificar si la respuesta parece ser HTML
      if (
        text.trim().startsWith("<!DOCTYPE") ||
        text.includes("<html") ||
        text.includes("<body")
      ) {
        // Parsear el HTML para extraer el mensaje de error
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, "text/html");
        // Buscar un elemento que contenga el mensaje de error (ajusta el selector según tu HTML)
        const errorMessage =
          doc.querySelector("p")?.innerHTML.trim() ||
          "Error desconocido en la respuesta del servidor";

        Swal.fire({
          icon: "error",
          title: "Error en el servidor",
          html: `<style>code.url { background-color: #eeeeee; font-family: monospace; padding: 0 2px; }</style>${errorMessage}`,
        });
        return; // Salir para no intentar parsear HTML como JSON
      }
      const json = JSON.parse(text);
      if (json.success) {
        if (esEditar) {
          // Para modo edición: Swal sin opciones, luego ejecuta btnCerrar.click()
          Swal.fire({
            icon: "success",
            title: "Usuario " + formMode,
            text: json.message,
            timer: 2000,
            showConfirmButton: false,
          }).then(() => {
            btnCerrar?.click();
            if (btnSearch) {
              btnSearch.disabled = false;
              btnSearch.click();
              btnSearch.disabled = true;
            }
          });
        } else {
          // Para modo no edición: Swal con opciones "Agregar otro" o "Regresar"
          Swal.fire({
            icon: "success",
            title: "Usuario " + formMode,
            text: json.message,
            showConfirmButton: true,
            confirmButtonText: "Agregar otro",
            showCancelButton: true,
            cancelButtonText: "Regresar",
          }).then((result) => {
            if (result.isConfirmed) {
              // Opción "Agregar otro": vaciar formulario
              vaciarFormUsuario(form);
            } else {
              // Opción "Regresar": volver atrás
              history.back();
            }
          });
        }
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: json.message,
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
      });
    } finally {
      if(button) button.disabled = false;
      switchModal(spinner);
    }
  });

  async function cargarAreas() {
    try {
      const response = await fetch(
        base + "controller/usuarios/obtener.php?areas=true"
      ); // Ajusta la ruta
      const json = await response.json();
      if (json.success) {
        const areasLegend = formUsuario.querySelector("#areas-legend"); // ID del <select>
        // selectArea.innerHTML = '<option value="">Seleccione un área</option>'; // Opción por defecto

        // Iterar sobre las áreas en json.data
        if(json.data){
          const areas = json.data;
          areasLegend.innerHTML = Object.keys(areas)
                      .map(area => `<span class="mr-1 text-center text-gray-400 text-bold text-[10px] px-1 py-1">${area}</span>`)
                      .join('');
        }else {
                throw new Error(data.message || 'No se encontraron áreas');
            }
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: json.message || "No se pudieron cargar las áreas",
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: `Error al cargar las áreas: ${error.message}`,
      });
    }
  }
});
