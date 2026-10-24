window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const table = document.getElementById('dataTable') || document.getElementById('datatablesSimple');
    if (table && typeof simpleDatatables !== 'undefined') {
        new simpleDatatables.DataTable(table);
    }
});
