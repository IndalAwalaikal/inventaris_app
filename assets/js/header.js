
        // Update waktu secara real-time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        // Update setiap detik
        setInterval(updateTime, 1000);
        updateTime();
        
        // Fitur pencarian global dengan dropdown hasil
        let searchTimeout;
        const searchInput = document.getElementById('global-search');
        const searchResults = document.getElementById('search-results');
        
        // Data menu/halaman yang bisa dicari
        const searchData = Array.from(document.querySelectorAll('a, button, [data-searchable="true"]')).map(el => {
            let title = el.textContent.trim();
            let url = el.href || (el.tagName === 'BUTTON' ? '' : null);
            let type = el.tagName === 'A' ? 'Halaman' : 'Aksi';
            let desc = el.getAttribute('title') || el.getAttribute('aria-label') || '';
            return { title, url, type, desc };
        }).filter(item => item.title && item.url !== null);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            if (searchTerm.length > 0) {
                searchTimeout = setTimeout(() => {
                    performGlobalSearch(searchTerm);
                }, 200);
            } else {
                hideSearchResults();
            }
        });
        
        function performGlobalSearch(searchTerm) {
            const filteredResults = searchData.filter(item => 
                item.title.toLowerCase().includes(searchTerm) ||
                item.desc.toLowerCase().includes(searchTerm)
            );
            
            displaySearchResults(filteredResults, searchTerm);
        }
        
        function displaySearchResults(results, searchTerm) {
            searchResults.innerHTML = '';
            
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="no-results">Tidak ada hasil ditemukan</div>';
            } else {
                results.forEach(item => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <p class="search-result-title">
                            ${highlightMatch(item.title, searchTerm)}
                            <span class="search-result-badge">${item.type}</span>
                        </p>
                        <p class="search-result-desc">${highlightMatch(item.desc, searchTerm)}</p>
                    `;
                    
                    resultItem.addEventListener('click', () => {
                        window.location.href = item.url;
                    });
                    
                    searchResults.appendChild(resultItem);
                });
            }
            
            showSearchResults();
        }
        
        function highlightMatch(text, searchTerm) {
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<strong style="background: #ffeb3b; padding: 1px 3px; border-radius: 2px;">$1</strong>');
        }
        
        function showSearchResults() {
            searchResults.style.display = 'block';
        }
        
        function hideSearchResults() {
            searchResults.style.display = 'none';
        }
        
        // Tutup hasil pencarian ketika klik di luar
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-section')) {
                hideSearchResults();
            }
        });
        
        // Navigasi keyboard
        searchInput.addEventListener('keydown', function(e) {
            const items = searchResults.querySelectorAll('.search-result-item');
            const currentActive = searchResults.querySelector('.search-result-item.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentActive) {
                    currentActive.classList.remove('active');
                    const next = currentActive.nextElementSibling;
                    if (next) {
                        next.classList.add('active');
                    } else {
                        items[0]?.classList.add('active');
                    }
                } else {
                    items[0]?.classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentActive) {
                    currentActive.classList.remove('active');
                    const prev = currentActive.previousElementSibling;
                    if (prev) {
                        prev.classList.add('active');
                    } else {
                        items[items.length - 1]?.classList.add('active');
                    }
                } else {
                    items[items.length - 1]?.classList.add('active');
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentActive) {
                    currentActive.click();
                }
            } else if (e.key === 'Escape') {
                hideSearchResults();
                searchInput.blur();
            }
        });
        
        // Tambahkan style untuk item aktif
        const style = document.createElement('style');
        style.textContent = `
            .search-result-item.active {
                background-color: #e3f2fd !important;
                border-left: 3px solid #007bff;
            }
        `;
        document.head.appendChild(style);