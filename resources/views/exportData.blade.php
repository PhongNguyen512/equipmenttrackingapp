<table>
    <thead>
        <tr>
            <th>{{ $reportData->site_name }}</th>
        </tr>
        <tr>
            <th>{{ $reportData->date }}</th>
        </tr>
        <tr>
            <th>{{ $reportData->time }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData->equipment_class_list as $classRow)
            <tr>
                <th>{{ $classRow->equipment_class_name }}</th>
            </tr>
            <tr>
                @foreach ($classRow->equipment_list as $equipment)
                    <td>{{ $equipment->unit }}</td>
                    <td>{{ $equipment->equipment_status }}</td>
                    <td>{{ $equipment->est_date_of_repair }}</td>
                    <td>{{ $equipment->note }}</td>
                    <td>{{ $equipment->additional_detail }}</td>
                @endforeach        
            </tr>
        @endforeach
    </tbody>
</table>

{{dd("See me here?")}}