document.addEventListener('DOMContentLoaded', () => {

  const modal = document.getElementById('modal');
  const modalImg = document.getElementById('modalImg');
  const closeBtn = document.querySelector('.close-btn');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  const images = Array.from(document.querySelectorAll('.product-img'));
  let currentIndex = 0;

  if (!modal || !modalImg || images.length === 0) return;

  

  images.forEach((img, index) => {
    img.addEventListener('click', () => {
      currentIndex = index;
      modalImg.src = img.src;
      modal.classList.add('show');
    });
  });

  function updateImage() {
    modalImg.src = images[currentIndex].src;
  }

  prevBtn.addEventListener('click', e => {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateImage();
  });

  nextBtn.addEventListener('click', e => {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
  });

  closeBtn.addEventListener('click', () => {
    modal.classList.remove('show');
  });

  modal.addEventListener('click', e => {
    if (e.target === modal) modal.classList.remove('show');
  });

  document.addEventListener('keydown', e => {
    if ((e.key === 'Enter' || e.key === 'Escape') && modal.classList.contains('show')) {
      modal.classList.remove('show');
    }
  });

});
