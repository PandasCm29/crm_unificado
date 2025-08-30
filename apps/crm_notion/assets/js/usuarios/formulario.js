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
      validate: (value) => /^9\d{8}$/.test(value),
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

  fields.forEach((field) => {
    field.input.addEventListener("input", () => {
      const value = field.input.value;
      updateFieldStyle(
        field.input,
        field.input.nextElementSibling,
        value,
        field
      );
    });
  });

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

  const modalEditar = document.getElementById("modal-editar-usuario");
  const button = document.querySelector(".submit-btn");
  const spinner = button?.querySelector(".spinner");
  const btnCerrar = modalEditar?.querySelector("#btn-cerrar-modal");
  const formUsuario = document.getElementById("form-usuario");
  const btnSearch = document.getElementById("button-search-users");

  btnCerrar?.addEventListener("click", () => switchModal(modalEditar));

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

    if (!isValidForm) return;

    const form = e.target;
    const formData = new FormData(form);
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }

    const formMode = form.dataset.mode;
    const esEditar = formMode === "Editado";
    if (esEditar) {
      data.idusuario = idusuario;
    }

    try {
      if (button) button.disabled = true;
      switchModal(spinner, true);

      const response = await fetch(`${base}controller/usuarios/Notion/sync_to_notion_usuario.php`,
        {
          method: esEditar ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        }
      );

      const text = await response.text();
      if (text.includes("<!DOCTYPE") || text.includes("<html")) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, "text/html");
        const errorMessage =
          doc.querySelector("p")?.innerHTML.trim() ||
          "Error desconocido del servidor";

        return Swal.fire({
          icon: "error",
          title: "Error del servidor",
          html: errorMessage,
        });
      }

      const json = JSON.parse(text);

      if (json.success) {
        let title = `Usuario ${formMode}`;
        if (json.question) {
          Swal.fire({
            icon: "info",
            title: json.title,
            text: json.message,
            showCancelButton: true,
            confirmButtonText: "Sí, sincronizar",
            cancelButtonText: "No, gracias",
          }).then(async (result) => {
            data['sincronizar'] = result.isConfirmed;
            try {
              const syncResponse = await fetch(`${base}controller/usuarios/Notion/sync_to_notion_usuario.php`,
                {
                  method: esEditar ? "PUT" : "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify(data),
                }
              );

              const syncJson = await syncResponse.json();
              if(syncJson.success){
                if (esEditar) {
                  // Para modo edición: Swal sin opciones, luego ejecuta btnCerrar.click()
                  Swal.fire({
                    icon: "success",
                    title: title,
                    text: syncJson.message,
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
                    title: title,
                    text: syncJson.message,
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
              }else{
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: syncJson.message,
                });
              }
            } catch (err) {
              Swal.fire("Error", err.message, "error");
            }
          });
          return;
        }
        if (esEditar) {
          // Para modo edición: Swal sin opciones, luego ejecuta btnCerrar.click()
          Swal.fire({
            icon: "success",
            title: title,
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
            title: title,
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
      Swal.fire({ icon: "error", title: "Error", text: error.message });
    } finally {
      if (button) button.disabled = false;
      switchModal(spinner);
    }
  });

  function vaciarFormUsuario(form) {
    fields.forEach(({ input }) => {
      const msg = input.nextElementSibling;
      msg.textContent = "";
    });
    vaciarForm(form);
  }

  async function cargarAreas() {
    try {
      const response = await fetch(
        base + "controller/usuarios/obtener.php?areas=true"
      );
      const json = await response.json();

      if (json.success) {
        const selectArea = formUsuario.querySelector("#area");

        if (json.data) {
          const areas = json.data;

          selectArea.innerHTML = '<option value="">Seleccionar</option>';

          Object.entries(areas).forEach(([nombre, id]) => {
            const option = document.createElement("option");
            option.value = id;
            option.textContent = nombre;
            selectArea.appendChild(option);
          });
        } else {
          throw new Error(json.message || "No se encontraron áreas");
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