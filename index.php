<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML element Counter</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>HTML element counter</h1>

        <form id="elementCounterForm">
            <div class="form-group">
                <label for="url">URL:</label>
                <input type="url" id="url" name="url" required
                    placeholder="https://example.com"
                    pattern="https?://.+"
                    title="Please enter a valid URL starting with http:// or https://">
            </div>

            <div class="form-group">
                <label for="element">HTML Element:</label>
                <input type="text" id="element" name="element" required
                    pattern="[a-zA-Z][a-zA-Z0-9]*"
                    title="Please enter a valid HTML elemnt name (letters and numbers only, starting with a letter)"
                    placeholder="img, div, p, etc.">
            </div>

            <button type="submit" id="submitBtn">Count Elements</button>
        </form>

        <div id="loading" class="hidden">
            <div class="spinner"></div>
            <p>Processing your request...</p>
        </div>

        <div id="results" class="hidden">
            <h2>Request Results</h2>
            <div id="requestResults"></div>

            <h2>General Statistics</h2>
            <div id="statistics"></div>
        </div>

        <div id="error" class="error hidden"></div>
    </div>

    <script src="script.js"></script>
</body>

</html>