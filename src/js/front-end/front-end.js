window.onload = function () {
	new Vue({
		el: '#app',
		data () {
			return {
				info: null
			}
		},
		mounted () {
			axios
			.get('/wp-json/mt/v1/photographer')
			.then(response => (this.info = response.data))
		}
	});
}
