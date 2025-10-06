// This  contains javascript code to handle form submission, validation,
// AJAX requests, and DOM updates.
//Preferred Jquery since it is not forbidden in the test instructions.
$(document).ready(function() {
    $('#elementCounterForm').on('submit', function(e) {
        e.preventDefault();
        
        const url = $('#url').val().trim();
        const element = $('#element').val().trim();
        
        if (!isValidUrl(url)) {
            showError('Please enter a valid URL starting with http:// or https://');
            return;
        }
        
        if (!isValidElement(element)) {
            showError('Please enter a valid HTML element name (letters and numbers only, starting with a letter)');
            return;
        }
        
        processRequest(url, element);
    });
    // Here we will define helper functions to keep things organized.
    
    function isValidUrl(string) {//make surre url is valid and starts with http or https
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }
    /*This regex checks that the element name starts with a letter and contains
    only letters and numbers. I consulted with AI for the regex pattern*/ 
    function isValidElement(element) {   
        return /^[a-zA-Z][a-zA-Z0-9]*$/.test(element);
    }
    
    function processRequest(url, element) {
        /*At the start of every new form submission we show the loading state, hide previous  errors
        and hide previous results*/ 
        showLoading();
        hideError();
        hideResults();
        
        $('#submitBtn').prop('disabled', true);
        
        $.ajax({
            url: 'process.php',
            type: 'POST',
            data: {
                url: url,
                element: element
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayResults(response.data);
                } else {
                    showError(response.error || 'An unexpected error occurred');
                }
            },
            error: function(xhr, status, error) {
                showError('Request failed: ' + (xhr.responseJSON?.error || error));
            },
            complete: function() {
                hideLoading();
                $('#submitBtn').prop('disabled', false);
            }
        });
    }
    
    function displayResults(data) {
        const requestHtml = `
            <div class="result-item">
                <strong>URL ${data.request.url}</strong> fetched on ${data.request.date}<br>
                Took ${data.request.duration}ms<br>
                Element &lt;${data.request.element}&gt; appeared ${data.request.count} times in page.
            </div>
        `;
        
        const statsHtml = `
            <div class="stat-item">
                <strong>${data.statistics.domain_urls}</strong> different URLs from ${data.statistics.domain} have been fetched
            </div>
            <div class="stat-item">
                Average fetch time from ${data.statistics.domain} during the last 24 hours is ${data.statistics.avg_duration}ms
            </div>
            <div class="stat-item">
                There was a total of <strong>${data.statistics.domain_element_count}</strong> &lt;${data.request.element}&gt; elements from ${data.statistics.domain}
            </div>
            <div class="stat-item">
                Total of <strong>${data.statistics.total_element_count}</strong> &lt;${data.request.element}&gt; elements counted in all requests ever made.
            </div>
        `;
        
        $('#requestResults').html(requestHtml);
        $('#statistics').html(statsHtml);
        $('#results').removeClass('hidden');
    }
    
    function showLoading() {
        $('#loading').removeClass('hidden');
    }
    
    function hideLoading() {
        $('#loading').addClass('hidden');
    }
    
    function showError(message) {
        $('#error').text(message).removeClass('hidden');
    }
    
    function hideError() {
        $('#error').addClass('hidden');
    }
    
    function hideResults() {
        $('#results').addClass('hidden');
    }
});