function viewImage(imageUrl) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');

    modalImage.src = imageUrl;
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
}