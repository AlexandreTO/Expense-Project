// assets/js/app.js

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('table');
    const headers = table.querySelectorAll('th a.sortable');
    let sortDirections = {}; // Track sort directions for each column

    headers.forEach(header => {
        header.addEventListener('click', function(event) {
            event.preventDefault();
            const sortField = this.dataset.sort;

            // Toggle the sort direction
            sortDirections[sortField] = !sortDirections[sortField]; // Default to ascending
            const isAscending = sortDirections[sortField];

            // Grab all rows
            const rows = Array.from(table.querySelectorAll('tbody tr'));

            // Sort rows based on the clicked header
            rows.sort((a, b) => {
                const aText = a.querySelector(`td[data-column="${sortField}"]`);
                const bText = b.querySelector(`td[data-column="${sortField}"]`);

                // Check if the text elements exist
                if (!aText || !bText) return 0;

                const aValue = aText.innerText.trim();
                const bValue = bText.innerText.trim();

                // Handle sorting based on the type of data
                let comparison = 0;
                switch (sortField) {
                    case 'amount':
                        comparison = parseFloat(aValue) - parseFloat(bValue); // Sort numerically
                        break;
                    case 'date':
                        comparison = new Date(aValue) - new Date(bValue); // Sort dates
                        break;
                    default:
                        comparison = aValue.localeCompare(bValue); // Sort strings
                }

                // Return sorted based on the direction
                return isAscending ? comparison : -comparison;
            });

            // Re-attach the sorted rows to the table body
            rows.forEach(row => table.querySelector('tbody').appendChild(row));
        });
    });
});
