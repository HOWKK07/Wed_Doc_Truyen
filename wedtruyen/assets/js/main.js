function filterTheLoai() {
    const searchValue = document.getElementById('the_loai_search').value.toLowerCase();
    const items = document.querySelectorAll('.the_loai_item');
    const listContainer = document.getElementById('the_loai_list');

    if (searchValue.trim() === '') {
        listContainer.style.display = 'none';
        return;
    }

    listContainer.style.display = 'block';
    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        item.style.display = name.includes(searchValue) ? 'block' : 'none';
    });
}