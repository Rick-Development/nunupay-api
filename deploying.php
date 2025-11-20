<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrating to CPanel - Coming Soon</title>
     <!--Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .coming-soon {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #6c757d;
        }
        .progress {
            height: 20px;
        }
    </style>
</head>
<body>

    <div class="coming-soon"> 
        <h3>Deploying to CPanel</h3>
        <p>We're currently deploying changes to CPanel. Please check back soon!</p>
         Progress Bar 
        <div class="progress mb-4">
            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 71%;" aria-valuenow="32" aria-valuemin="0" aria-valuemax="100">71% Complete</div>
        </div>
            <!--<button id="notifyButton" class="btn btn-success">Done</button>-->
    </div>

     <!--Bootstrap JS and jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#notifyButton').click(function() {
            alert('You will be notified when the migration is complete!');
        });
    </script>

</body>
</html>