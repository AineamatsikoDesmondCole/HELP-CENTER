    </div> <!-- Close container -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Live Search Script -->
    <script>
    document.getElementById('liveSearch').addEventListener('input', function(e) {
        let query = e.target.value;
        let resultsDiv = document.getElementById('searchResults');
        
        if (query.length < 2) {
            resultsDiv.classList.add('d-none');
            return;
        }
        
        fetch(`api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '<div class="list-group">';
                    data.forEach(faq => {
                        html += `<a href="faq.php?id=${faq.id}" class="list-group-item list-group-item-action">
                                    <strong>${faq.question}</strong><br>
                                    <small class="text-muted">${faq.snippet}</small>
                                </a>`;
                    });
                    html += '</div>';
                    resultsDiv.innerHTML = html;
                    resultsDiv.classList.remove('d-none');
                } else {
                    resultsDiv.innerHTML = '<div class="p-3 text-muted">No results found</div>';
                    resultsDiv.classList.remove('d-none');
                }
            });
    });
    
    // Hide results when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#liveSearch')) {
            document.getElementById('searchResults').classList.add('d-none');
        }
    });
    </script>
    
<script>
// Live Search Functionality
let searchTimeout;

document.getElementById('liveSearch').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    const resultsDiv = document.getElementById('searchResults');
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Hide if query is too short
    if (query.length < 2) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Show loading
    resultsDiv.innerHTML = '<div class="list-group-item text-muted">Searching...</div>';
    resultsDiv.style.display = 'block';
    
    // Debounce - wait 300ms after user stops typing
    searchTimeout = setTimeout(() => {
        fetch(`api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    resultsDiv.innerHTML = `<div class="list-group-item text-danger">${data.error}</div>`;
                    return;
                }
                
                if (data.length === 0) {
                    resultsDiv.innerHTML = `
                        <div class="list-group-item">
                            <div class="text-muted">No results found for "<strong>${query}</strong>"</div>
                            <a href="support.php" class="small text-primary">Try contacting support instead</a>
                        </div>`;
                    return;
                }
                
                let html = '';
                data.forEach(item => {
                    html += `
                    <a href="faq.php?id=${item.id}" class="list-group-item list-group-item-action">
                        <div class="fw-bold">${item.question}</div>
                        <small class="text-muted">${item.snippet}</small>
                    </a>`;
                });
                
                // Add "View all results" link
                html += `
                <div class="list-group-item bg-light">
                    <a href="index.php?search=${encodeURIComponent(query)}" class="text-primary small">
                        <i class="fas fa-external-link-alt me-1"></i>
                        View all results for "${query}"
                    </a>
                </div>`;
                
                resultsDiv.innerHTML = html;
            })
            .catch(error => {
                resultsDiv.innerHTML = `
                <div class="list-group-item text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Search temporarily unavailable
                </div>`;
                console.error('Search error:', error);
            });
    }, 300); // 300ms debounce
});

// Hide results when clicking elsewhere
document.addEventListener('click', function(e) {
    const searchResults = document.getElementById('searchResults');
    const liveSearch = document.getElementById('liveSearch');
    
    if (!e.target.closest('.search-container')) {
        searchResults.style.display = 'none';
    }
});

// Keyboard navigation
document.getElementById('liveSearch').addEventListener('keydown', function(e) {
    const resultsDiv = document.getElementById('searchResults');
    const links = resultsDiv.querySelectorAll('a');
    
    if (e.key === 'Escape') {
        resultsDiv.style.display = 'none';
        this.blur();
    } else if (e.key === 'ArrowDown' && links.length > 0) {
        e.preventDefault();
        links[0].focus();
    }
});
</script>
</body>
</html>