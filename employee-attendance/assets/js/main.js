if (window.attendanceChartData) {
  const ctx = document.getElementById('attendanceChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: window.attendanceChartData.labels,
        datasets: [{
          label: 'Present Days',
          data: window.attendanceChartData.values,
          backgroundColor: '#198754'
        }]
      },
      options: {scales:{y:{beginAtZero:true}}}
    });
  }
}
