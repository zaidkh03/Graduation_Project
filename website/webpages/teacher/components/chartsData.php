<script>
  $(function () {
    //-------------
    //- PIE CHART -
    //-------------
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
    var pieData = {
      labels: ['Chrome', 'IE', 'FireFox', 'Safari', 'Opera', 'Navigator'],
      datasets: [{
        data: [700, 500, 400, 600, 300, 100],
        backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
      }]
    }
    var pieOptions = {
      maintainAspectRatio: false,
      responsive: true,
    }
    new Chart(pieChartCanvas, {
      type: 'pie',
      data: pieData,
      options: pieOptions
    })

    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas = $('#barChart').get(0).getContext('2d')
    var barChartData = {
      labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
      datasets: [
        {
          label: 'Electronics',
          backgroundColor: 'rgba(210, 214, 222, 1)',
          borderColor: 'rgba(210, 214, 222, 1)',
          data: [65, 59, 80, 81, 56, 55, 40]
        },
        {
          label: 'Digital Goods',
          backgroundColor: 'rgba(60,141,188,0.9)',
          borderColor: 'rgba(60,141,188,0.8)',
          data: [28, 48, 40, 19, 86, 27, 90]
        }
      ]
    }
    var barChartOptions = {
      responsive: true,
      // maintainAspectRatio: false,
      datasetFill: false
    }

    new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    })
  })
</script>