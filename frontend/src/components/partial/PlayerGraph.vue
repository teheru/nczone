<script>
import { Line } from 'vue-chartjs'

export default {
  extends: Line,
  name: 'nczone-player-graph',
  props: {
    matchNumbers: {
      type: Object,
      required: true
    },
    ratings: {
      type: Object,
      required: true
    }
  },
  mounted () {
    this.renderChart({
      labels: this.matchNumbers,
      datasets: [{
        data: this.ratings,
        pointRadius: 1
      }]
    },
    {
      maintainAspectRatio: false,
      legend: {display: false},
      tooltips: {enabled: false},
      scales: {
        xAxes: [{
          display: false
        }]
      }
    })
  }
}
</script>
