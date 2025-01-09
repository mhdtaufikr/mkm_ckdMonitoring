<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="{{ route('delivery-note.detail', ['id' => encrypt($row->id)]) }}" title="Details">
                <i class="fas fa-info-circle" style="margin-right: 5px;"></i> Details
            </a>
        </li>
        <li>
            <a href="{{ route('delivery-note.pdf', ['id' => encrypt($row->id)]) }}" class="dropdown-item" title="Generate PDF">
                <i class="fas fa-file-pdf" style="margin-right: 5px;"></i> Generate PDF
            </a>
        </li>
    </ul>
</div>
