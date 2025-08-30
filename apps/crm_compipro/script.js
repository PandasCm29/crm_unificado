document.addEventListener("DOMContentLoaded", () => {

//FLATEPICKR
// flatpickr(".datepicker", {
//     altInput: true,
//     altFormat: "d/m/Y",
//     dateFormat: "Y-m-d",
//     allowInput: true,
//     locale: {
//         firstDayOfWeek: 1,
//         weekdays: {
//         shorthand: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"],
//         longhand:  ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"]
//         },
//         months: {
//         shorthand: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
//         longhand:  ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]
//         }
//     }
// });
  // Filtro rango de fechas
  flatpickr("#fecha-rango", {
    mode: "range",
    dateFormat: "d/m/Y",
    closeOnSelect: false,
    onChange: function (selectedDates, dateStr, instance) {
      const desdeEl = document.getElementById("fecha-desde");
      const hastaEl = document.getElementById("fecha-hasta");
      hastaEl.style.display = 'block';
      if (selectedDates.length === 2) {
        const [start,end] = selectedDates;
        desdeEl.textContent = "Desde: " + instance.formatDate(start,"d/m/Y");
        hastaEl.textContent = "Hasta: " + instance.formatDate(end,  "d/m/Y");
      } else if (selectedDates.length === 1) {
        desdeEl.textContent = "Desde: " + instance.formatDate(selectedDates[0],"d/m/Y");
        hastaEl.textContent = "";
      } else {
        desdeEl.textContent = "";
        hastaEl.textContent = "";
      }
    }
  });
});