let dataTable = new DataTable('#myTable');
dataTable.on('click', 'tbody tr .js-admin-article-delete', function () {
    console.log('test')
    this.closest('td').querySelector('.popupWrapper').style.display = "block"
});