/**
 * Global Live Search Module - Gift Shop POS
 *
 * Automatically attaches to free-text search inputs across list/index pages.
 * Triggers live asynchronous search on keyup / input with debouncing.
 * Replaces only the #live-search-container (table + pagination + counts).
 * Preserves all active filter parameters, permissions, and query structure.
 * Leaves dropdown filters manual until the user clicks Apply Filter.
 */
document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = document.querySelectorAll('input[name="search"]');
    if (!searchInputs.length) return;

    searchInputs.forEach(input => {
        const form = input.closest('form');
        const container = document.getElementById('live-search-container')
            || document.querySelector('.card:has(table)')
            || document.querySelector('.table-responsive')?.closest('.card');

        if (!container) return;

        let debounceTimer = null;
        let activeController = null;
        let lastQuery = input.value;

        // Visual search icon indicator
        const inputGroup = input.closest('.input-group');
        const searchIcon = inputGroup ? inputGroup.querySelector('.fa-search, .fa-spinner') : null;
        let originalIconClasses = searchIcon ? searchIcon.className : '';

        function setSearching(isSearching) {
            if (searchIcon) {
                if (isSearching) {
                    searchIcon.className = 'fas fa-spinner fa-spin text-primary';
                } else {
                    searchIcon.className = originalIconClasses;
                }
            }
            if (isSearching) {
                container.style.transition = 'opacity 0.2s ease';
                container.style.opacity = '0.55';
                container.style.pointerEvents = 'none';
            } else {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        }

        function performLiveSearch(query) {
            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            // Build target URL preserving all other active form filter inputs
            let baseUrl = form && form.action ? form.action : window.location.pathname;
            let params = new URLSearchParams();

            if (form) {
                const formData = new FormData(form);
                for (let [key, val] of formData.entries()) {
                    if (key !== 'page' && key !== '_token') {
                        // Exclude empty values to keep URL clean, but keep values that have been set
                        if (val !== '') {
                            params.set(key, val);
                        }
                    }
                }
            } else {
                params = new URLSearchParams(window.location.search);
                params.delete('page');
            }

            const trimmedQuery = query.trim();
            if (trimmedQuery !== '') {
                params.set('search', trimmedQuery);
            } else {
                params.delete('search');
            }

            const queryString = params.toString();
            const targetUrl = baseUrl + (queryString ? '?' + queryString : '');

            setSearching(true);

            fetch(targetUrl, {
                signal: activeController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Search response error');
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newContainer = newDoc.getElementById('live-search-container');

                if (newContainer) {
                    container.innerHTML = newContainer.innerHTML;
                } else {
                    const fallback = newDoc.querySelector('.card:has(table)')
                        || newDoc.querySelector('.table-responsive')?.closest('.card');
                    if (fallback) {
                        container.innerHTML = fallback.innerHTML;
                    }
                }

                // Update browser URL bar silently without full reload
                window.history.replaceState({ path: targetUrl }, '', targetUrl);
                setSearching(false);
                attachPaginationHandlers();
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Live search error:', err);
                    setSearching(false);
                }
            });
        }

        function onSearchInput(immediate = false) {
            const currentVal = input.value;
            if (currentVal === lastQuery && !immediate) return;
            lastQuery = currentVal;

            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            if (immediate) {
                performLiveSearch(currentVal);
            } else {
                debounceTimer = setTimeout(() => {
                    performLiveSearch(currentVal);
                }, 300);
            }
        }

        // Live search on typing (input and keyup)
        input.addEventListener('input', function () {
            onSearchInput(false);
        });

        input.addEventListener('keyup', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                onSearchInput(true);
            }
        });

        // Prevent full page reload on Enter inside search input
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                onSearchInput(true);
            }
        });

        // If clicking a dedicated search button next to the input, run live search immediately
        if (inputGroup) {
            const searchBtn = inputGroup.querySelector('button[type="submit"], button.btn-primary');
            if (searchBtn) {
                searchBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    onSearchInput(true);
                });
            }
        }

        // Seamless pagination within the live search container
        function attachPaginationHandlers() {
            const paginationLinks = container.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const pageUrl = this.href;
                    if (!pageUrl || pageUrl === '#' || pageUrl.startsWith('javascript:')) return;

                    if (activeController) activeController.abort();
                    activeController = new AbortController();

                    setSearching(true);

                    fetch(pageUrl, {
                        signal: activeController.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const newDoc = parser.parseFromString(html, 'text/html');
                        const newContainer = newDoc.getElementById('live-search-container');
                        if (newContainer) {
                            container.innerHTML = newContainer.innerHTML;
                        }
                        window.history.pushState({ path: pageUrl }, '', pageUrl);
                        setSearching(false);
                        attachPaginationHandlers();
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            setSearching(false);
                        }
                    });
                });
            });
        }

        // Initial setup for pagination links
        attachPaginationHandlers();

        // Handle browser Back / Forward buttons smoothly
        window.addEventListener('popstate', function () {
            const searchParams = new URLSearchParams(window.location.search);
            const searchVal = searchParams.get('search') || '';
            if (input.value !== searchVal) {
                input.value = searchVal;
                performLiveSearch(searchVal);
            }
        });
    });
});

