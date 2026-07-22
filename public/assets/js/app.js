document.addEventListener('DOMContentLoaded', () => {
  const toast = document.querySelector('[data-toast]');
  if (toast) {
    setTimeout(() => toast.classList.add('opacity-0'), 5000);
  }
});
