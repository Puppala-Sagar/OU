<?php
// Define your PHP variables and logic here
$e_flag = 0; // Example variable; replace with your actual logic

// Define a dummy url_for function for illustration purposes
function url_for($path, $filename = null) {
    // Replace this with actual URL generation logic based on your framework
    // Concatenate $path and $filename to form the complete URL
    return $path . ($filename ? '/' . $filename : '');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RESULTS</title>
    <link href="<?php echo url_for('static', 'css/style.css'); ?>" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo url_for('static', 'images/kmitlogo.png'); ?>">
</head>
<body>
    <header class="head">
        <div>
            <a href="https://www.osmania.ac.in/">
                <img class="img9" src="<?php echo url_for('static', 'images/oulogo.png'); ?>" alt="oulogo">
            </a>
        </div>
        <div>
            <a href="https://www.kmit.in/">
                <img class="img8" src="<?php echo url_for('static', 'images/sponsorlogo.png'); ?>" alt="logo" height="75px" width="120px;">
            </a>
        </div>
    </header>

    <?php if ($e_flag != 0): ?>
        <div class="popup-container <?php if ($e_flag == 1) echo 'visible'; else echo 'hidden'; ?>" id="popup1">
            <div class="popup-content">
                <h2>URL ERROR</h2>
                <p>Please enter a valid OU results URL</p>
                <button class="btnerr" onclick="hidePopup()">Close</button>
            </div>
        </div>
        <div class="popup-container <?php if ($e_flag == 2) echo 'visible'; else echo 'hidden'; ?>" id="popup2">
                    <div class="popup-content">
                        <h2>INPUT ERROR</h2>
                        <p>Please enter the roll numbers </p>
                        <h3>OR</h3>
                        <p>Choose the CSV file containing roll numbers </p>
                        <button class="button-54" onclick="hidePopup()">Close</button>
                    </div>
                </div>
                <div class="popup-container <?php if ($e_flag == 3) echo 'visible'; else echo 'hidden'; ?>" id="popup3">
                    <div class="popup-content">
                        <h2>Invalid ROll numbers</h2>
                        <p>Enter valid starting and ending roll number</p>
                        <button class="button-54" onclick="hidePopup()">Close</button>
                    </div>
                </div>
                <div class="popup-container <?php if ($e_flag == 4) echo 'visible'; else echo 'hidden'; ?>" id="popup4">
                    <div class="popup-content">
                        <h2>Irrelevant data</h2>
                        <p>Given file contains data other than roll numbers<br>PLease check the
                            input file once again</p>
                        <button class="button-54" onclick="hidePopup()">Close</button>
                    </div>
                </div>
                <div class="popup-container <?php if ($e_flag == 5) echo 'visible'; else echo 'hidden'; ?>" id="popup5">
                    <div class="popup-content">
                        <h2>Invalid data</h2>
                        <p>File contains invalid roll numbers</p>
                        <button class="button-54" onclick="hidePopup()">Close</button>
                    </div>
                </div>
                <div class="popup-container <?php if ($e_flag == 6) echo 'visible'; else echo 'hidden'; ?>" id="popup6">
                    <div class="popup-content">
                        <h2>OU SERVER ERROR</h2>
                        <p>Please try again later the server is busy</p>
                        <button class="button-54" onclick="hidePopup()">Close</button>
                    </div>
               </div>

            <script>
                    // Function to hide the popup
                    function hidePopup() {
                        var popup1 = document.getElementById('popup1');
                        var popup2 = document.getElementById('popup2');
                        var popup3 = document.getElementById('popup3');
                        var popup4 = document.getElementById('popup4');
                        var popup5 = document.getElementById('popup5');
                        var popup6 = document.getElementById('popup6');
                        popup1.classList.remove('visible');
                        popup1.classList.add('hidden');
                        popup2.classList.remove('visible');
                        popup2.classList.add('hidden');
                        popup3.classList.remove('visible');
                        popup3.classList.add('hidden');
                        popup4.classList.remove('visible');
                        popup4.classList.add('hidden');
                        popup5.classList.remove('visible');
                        popup5.classList.add('hidden');
                        popup6.classList.remove('visible');
                        popup6.classList.add('hidden');
                    }
            </script>
    <?php endif; ?>

    <form class="frm" method="POST" enctype="multipart/form-data" action="/getres" novalidate>
        <div>
            <label> URL </label>
            <input class="url" type="text" name="URL">
        </div>
        <div>
            <label> ENTER STARTING ROLL NUMBER </label>
            <input  class="inpts" type="text" name="num1">
        </div>
        <div>
            <label> ENTER ENDING ROLL NUMBER </label>
            <input class="inpts" type="text" name="num2">
        </div>
        <p class="or"><b>OR</b></p>
        <label>CHOOSE THE CSV FILE WITH ROLL NUMBERS</label>
        <input class="files" type="file" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <div  class="btn" id="content">
            <button id="startButton" class="button-29" onclick="showLoader()">SUBMIT AND DOWNLOAD</button>
        </div>
        <div class="loader-container">
            <div class="loading" id="loading">
                <div class="spinner"></div>
            </div>
        </div>
    </form>

    <script>
        document.getElementById("startButton").addEventListener("click", function() {
            // Show the loading spinner
            document.getElementById("loading").style.display = "block";

            // Simulate a check for the session flag
            checkSessionFlag();
        });

      document.getElementById("startButton").addEventListener("click", function() {
        // Show the loading spinner
        document.getElementById("loading").style.display = "block";

        // Simulate a check for the session flag
        checkSessionFlag();
      });

      function checkSessionFlag() {
        // Make an asynchronous request to check the session flag
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.fileReady) {
              // Hide the loading spinner when the file is ready
              document.getElementById("loading").style.display = "none";

              // Reset the session flag
              resetSessionFlag();
            } else {
              // If the file is not ready, continue checking
              setTimeout(checkSessionFlag, 100); // Check again in .1 second (adjust as needed)
            }
          }
        };

        xhr.open("GET", "/check_session_flag", true);
        xhr.send();
      }

      function resetSessionFlag() {
        // Make an asynchronous request to reset the session flag
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/reset_session_flag", true);
        xhr.send();
      }
    </script>

    <div class="box">
        <h2>INSTRUCTIONS:</h2>
        <p>
            1. Enter a proper URL corresponding to the OU results website
            E.g :- https://www.osmania.ac.in/res07/20230788.jsp<br/>
            2. Make sure that the roll number you entered is a valid 12 digit number  <br/>
            3. The roll number file must be a CSV File (OR) Excel File
            and the file should contain nothing except Roll Numbers in a column <br/>
            4. In the file , it's suggested to have a Column name for the roll numbers ,
            and make sure that they stay in the first column.<br/>
            5. If there's a Load on the server , Please try after a couple of minutes.<br/>
        </p>
        <br>
        <div class="abt">
            <details>
                <summary><h1>ABOUT US</h1></summary>
                <p>This website is made by a passionate team consisting of K. Baba Gandhi, V. Bharadwaj Reddy, and
                    P. Sagar (2022-2026 batch of KMIT). Our collective goal is to simplify the lives of employees
                    across all colleges affiliated with Osmania University. With a firm belief in the power of
                    technology and innovation, we have crafted a platform that streamlines the process of
                    extracting and organizing result data. Our commitment to providing accurate and convenient
                    Excel sheets enables them to access the academic results effortlessly.</p>
            </details>
        </div>
    </div>

    <div class="foot" >KMIT &#169 2022-2026 (V.Bharadwaj Reddy , K.Baba Gandhi , P.Sagar)</div>
</body>
</html>

