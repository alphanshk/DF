const categoryCanvas = document.getElementById('categoryChart');
const trendCanvas = document.getElementById('trendChart');

if (categoryCanvas && Array.isArray(window.categoryChartData)) {
  const labels = window.categoryChartData.map((item) => item.category);
  const values = window.categoryChartData.map((item) => Number(item.total));

  new Chart(categoryCanvas, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6'],
      }],
    },
  });
}

if (trendCanvas && Array.isArray(window.trendChartData)) {
  const labels = window.trendChartData.map((item) => item.day);
  const values = window.trendChartData.map((item) => Number(item.total));

  new Chart(trendCanvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Daily Spend',
        data: values,
        backgroundColor: '#3b82f6',
      }],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}
