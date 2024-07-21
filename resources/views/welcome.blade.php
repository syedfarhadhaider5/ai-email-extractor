<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload .rtf File</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        /* Adjust styling as needed */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
        }
        body{
            background-color: #f0f2f5;
        }
    </style>
</head>
<body>
<div id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-3 col-sm-12"></div>
        <div class="col-md-6 col-sm-12">
            <div style="background-color: #ffffff; border-radius: 4px; padding: 20px 10px; box-shadow: 0 6px 4px rgba(0, 0, 0, 0.1); margin-top: 100px;">
                <h4 class="mb-4 text-center">Upload .rtf File</h4>
                <form id="uploadForm" action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="custom-file mb-3">
                        <input type="file" accept=".rtf" class="custom-file-input" id="fileInput" name="file" accept=".rtf" onchange="uploadFile()">
                        <label class="custom-file-label" for="fileInput">Choose file</label>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-3  col-sm-12"></div>
    </div>
</div>

<script>
    function uploadFile() {
        document.getElementById('loadingOverlay').style.display = 'block';
        document.getElementById('uploadForm').submit();
    }
    setTimeout(endLoading, 20000); // 10000 milliseconds = 10 seconds

    function endLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
</script>
</body>
</html>
