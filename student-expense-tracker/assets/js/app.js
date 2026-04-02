// Dark mode toggle with localStorage memory.
const toggleButton = document.getElementById('darkModeToggle');

if (localStorage.getItem('student_tracker_theme') === 'dark') {
  document.body.classList.add('dark');
}

if (toggleButton) {
  toggleButton.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    const newTheme = document.body.classList.contains('dark') ? 'dark' : 'light';
    localStorage.setItem('student_tracker_theme', newTheme);
  });
}
