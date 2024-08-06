document.addEventListener('DOMContentLoaded', function () {
    const typeField = document.querySelector('#Setting_type');
    const valueField = document.querySelector('#Setting_value');
    const valueFieldWrapper = valueField.parentElement;

    if (!typeField || !valueField || !valueFieldWrapper) {
        console.error('Required form fields not found');
        return;
    }

    // Hide value field
    valueField.classList.add('d-none');

    function updateValueField() {
        const selectedType = typeField.value;
        const existingDynamicField = valueFieldWrapper.querySelector('.dynamic-field');

        if (existingDynamicField) {
            existingDynamicField.remove();
        }

        let newField;

        switch (selectedType) {
            case 'integer':
                newField = document.createElement('input');
                newField.type = 'number';
                newField.classList.add('form-control', 'dynamic-field');
                newField.value = parseInt(valueField.value, 10) || '';
                break;
            case 'boolean':
                newField = document.createElement('input');
                newField.type = 'checkbox';
                newField.classList.add('form-check-input', 'dynamic-field');
                newField.checked = valueField.value === 'true';
                break;
            case 'string':
            default:
                newField = document.createElement('input');
                newField.type = 'text';
                newField.classList.add('form-control', 'dynamic-field');
                newField.value = valueField.value;
                break;
        }

        // Update hidden field
        newField.addEventListener('change', () => {
            if (selectedType === 'boolean') {
                valueField.value = newField.checked ? 'true' : 'false';
            } else {
                valueField.value = newField.value;
            }
        });

        // Add new field in wrapper
        valueFieldWrapper.insertBefore(newField, valueField);
    }

    typeField.addEventListener('change', updateValueField);
    updateValueField();
});
