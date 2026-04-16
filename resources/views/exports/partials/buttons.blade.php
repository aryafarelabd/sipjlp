{{--
  Partial: tombol export Excel & PDF
  @param string $route  — nama route export, misal 'export.logbook-limbah'
  @param array  $params — query params tambahan (bulan, tahun, filter, dsb)
--}}
@php $params ??= []; @endphp
<div class="btn-group">
    <a href="{{ route($route, array_merge($params, ['format' => 'xlsx'])) }}"
       class="btn btn-sm btn-outline-success" title="Export Excel">
        <i class="ti ti-file-spreadsheet me-1"></i>Excel
    </a>
    <a href="{{ route($route, array_merge($params, ['format' => 'pdf'])) }}"
       class="btn btn-sm btn-outline-danger" title="Export PDF" target="_blank">
        <i class="ti ti-file-type-pdf me-1"></i>PDF
    </a>
</div>
