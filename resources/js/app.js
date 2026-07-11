import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.requestRows = (initialRows = []) => ({
    rows: initialRows.length > 0
        ? initialRows.map((row) => ({ ...window.blankRequestRow(), ...row }))
        : [window.blankRequestRow()],
    addRow() {
        this.rows.push(window.blankRequestRow());
    },
    removeRow(index) {
        if (this.rows.length === 1) {
            this.rows[0] = window.blankRequestRow();
            return;
        }

        this.rows.splice(index, 1);
    },
});

window.blankRequestRow = () => ({
    weld_number: '',
    title_id: '',
    drawing_id: '',
    line_id: '',
    diameter: '',
    thickness: '',
    material_1_id: '',
    material_2_id: '',
    welded_at: '',
    welding_process_id: '',
    weld_type_id: '',
    pipeline_category_id: '',
    medium_id: '',
    pwht: '',
    normative_document_id: '',
});

Alpine.start();
