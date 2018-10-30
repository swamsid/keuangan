<table border="1" width="80%" style="border-collapse: collapse;">
	<thead>
		<tr>
			<td>Id Periode</td>
			<td>Keterangan Periode</td>
			<td>Status Periode</td>
		</tr>
	</thead>

	<tbody>
		@foreach($data as $key => $periode)
			<tr>
				<td>{{ $periode->pk_id }}</td>
				<td>{{ $periode->pk_periode }}</td>
				<td>{{ $periode->pk_status }}</td>
			</tr>
		@endforeach
	</tbody>
</table>