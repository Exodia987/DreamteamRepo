<?php
// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "Keszenallok01!";
$dbname = "user_auth";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for logged-in user
$username = '';
if (isset($_COOKIE['auth_token'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['auth_token']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = htmlspecialchars($user['username']);
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern T-Shirt Customizer - Digiprint</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --background-color: #f4f4f4;
            --text-color: #333;
        }
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav ul li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 600;
        }
        .customizer-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 2rem;
            padding: 2rem;
        }
        .tshirt-preview {
            width: 300px;
            height: 300px;
            margin: 2rem auto;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .options-row {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        .option-button {
            flex: 1;
            padding: 1rem;
            margin: 0 0.5rem;
            border: none;
            background-color: #e0e0e0;
            color: #333;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        .option-button.selected {
            background-color: var(--primary-color);
            color: white;
        }
        .option-button.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            margin-left: 0.5rem;
        }
        .customization-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #45a049;
        }
        #imageUpload {
            display: none;
        }
        .customization-item {
            position: absolute;
            cursor: move;
        }
        .customization-item img {
            max-width: 100px;
            max-height: 100px;
        }
        .back-button {
            display: none;
            margin-top: 1rem;
        }
        .option {
            flex: 1 0 calc(33.333% - 1rem);
            max-width: calc(33.333% - 1rem);
            aspect-ratio: 1;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .option:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .option.selected {
            border-color: var(--primary-color);
        }
        .option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">T-Shirt Customizer</a></li>
                    <?php if ($username): ?>
                        <li><span>Welcome, <?php echo $username; ?></span></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="customizer-container">
            <h1>T-Shirt Customizer</h1>
            <div class="tshirt-preview" id="tshirtPreview"></div>
            
            <div id="step1" class="step active">
                <h2>Step 1: Select T-Shirt Type</h2>
                <div class="options">
                    <div class="option" data-type="male">
                        <img src="https://image.hm.com/assets/hm/e5/1e/e51ee44b43810cde673022b8185bc611917944f3.jpg?imwidth=2160" alt="Male T-Shirt">
                    </div>
                    <div class="option" data-type="female">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Female T-Shirt">
                    </div>
                </div>
            </div>

            <div id="step2" class="step">
                <h2>Step 2: Choose Color</h2>
                <div class="options">
                    <div class="option" data-color="white">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="White T-Shirt">
                    </div>
                    <div class="option" data-color="black">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Black T-Shirt">
                    </div>
                    <div class="option" data-color="red">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Red T-Shirt">
                    </div>
                    <div class="option" data-color="green">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Green T-Shirt">
                    </div>
                    <div class="option" data-color="grey">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Grey T-Shirt">
                    </div>
                    <div class="option" data-color="yellow">
                        <img src="https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]" alt="Yellow T-Shirt">
                    </div>
                </div>
            </div>

            <div id="step3" class="step">
                <h2>Step 3: Select Size</h2>
                <div class="options">
                    <div class="option" data-size="xs">XS</div>
                    <div class="option" data-size="s">S</div>
                    <div class="option" data-size="m">M</div>
                    <div class="option" data-size="l">L</div>
                    <div class="option" data-size="xl">XL</div>
                    <div class="option" data-size="xxl">XXL</div>
                </div>
            </div>

            <div id="step4" class="step">
                <h2>Step 4: Customize Your T-Shirt</h2>
                <div class="customization-tools">
                    <input type="file" id="imageUpload" accept="image/*">
                    <label for="imageUpload" class="btn">Upload Image</label>
                    <input type="text" id="customText" placeholder="Enter custom text">
                    <button id="addTextBtn" class="btn">Add Text</button>
                    <input type="color" id="textColor" value="#000000">
                    <select id="fontFamily">
                        <option value="Arial">Arial</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Courier">Courier</option>
                    </select>
                </div>
            </div>

            <div class="options-row">
                <button class="option-button" data-option="type">Type</button>
                <button class="option-button" data-option="color">Color</button>
                <button class="option-button" data-option="size">Size</button>
                <button class="option-button" data-option="customize">Customize</button>
            </div>

            <button id="backButton" class="btn back-button" style="display: none;">Back</button>
            <button id="saveDesign" class="btn">Save Design</button>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Digiprint. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tshirtPreview = document.getElementById('tshirtPreview');
            const steps = document.querySelectorAll('.step');
            const optionButtons = document.querySelectorAll('.option-button');
            const backButton = document.getElementById('backButton');
            const saveDesignBtn = document.getElementById('saveDesign');
            const imageUpload = document.getElementById('imageUpload');
            const addTextBtn = document.getElementById('addTextBtn');
            const customText = document.getElementById('customText');
            const textColor = document.getElementById('textColor');
            const fontFamily = document.getElementById('fontFamily');

            let currentStep = 0;
            let selectedType, selectedColor, selectedSize;

            const tshirtImages = {
                male: {
                    white: 'https://image.hm.com/assets/hm/e5/1e/e51ee44b43810cde673022b8185bc611917944f3.jpg?imwidth=2160',
                    black: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Fc0%2Ff7%2Fc0f704035fd062f6c51ae555c17af44d6a1bdc23.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    red: 'https://image.hm.com/assets/hm/c1/2b/c12b8375da2b0d53d0cc84f128626ef0a8b04b87.jpg?imwidth=2160',
                    green: 'https://image.hm.com/assets/hm/4d/bb/4dbbd0c380a644ea2e430a12a6ac1586668fe91d.jpg?imwidth=2160',
                    grey: 'https://image.hm.com/assets/hm/70/49/7049ee3b6c1631b3f3c2c34b32a485a679120f53.jpg?imwidth=2160',
                    yellow: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2F60%2Faa%2F60aaf5a23eaaf2347fd6608cb8c083805600be3f.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5Bmen_tshirtstanks_shortsleeve%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]'
                },
                female: {
                    white: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    black: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    red: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    green: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    grey: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]',
                    yellow: 'https://lp2.hm.com/hmgoepprod?set=quality%5B79%5D%2Csource%5B%2Ff2%2Fad%2Ff2ade3358f684c68901b11b96af105b4b2bc618b.jpg%5D%2Corigin%5Bdam%5D%2Ccategory%5B%5D%2Ctype%5BDESCRIPTIVESTILLLIFE%5D%2Cres%5Bm%5D%2Chmver%5B2%5D&call=url[file:/product/main]'
                }
            };

            function updateTshirtPreview() {
                if (selectedType && selectedColor) {
                    tshirtPreview.style.backgroundImage = `url(${tshirtImages[selectedType][selectedColor]})`;
                }
            }

            function showStep(stepIndex) {
                steps.forEach((step, index) => {
                    if (index === stepIndex) {
                        step.style.display = 'block';
                    } else {
                        step.style.display = 'none';
                    }
                });
                updateNavigationButtons();
            }

            function updateNavigationButtons() {
                backButton.style.display = currentStep > 0 ? 'inline-block' : 'none';
                optionButtons.forEach((btn, index) => {
                    if (index === currentStep) {
                        btn.classList.add('selected');
                    } else {
                        btn.classList.remove('selected');
                    }
                });
            }

            optionButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    currentStep = index;
                    showStep(currentStep);
                });
            });

            backButton.addEventListener('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            document.querySelectorAll('.option').forEach(option => {
                option.addEventListener('click', function() {
                    const step = this.closest('.step');
                    step.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');

                    if (step.id === 'step1') {
                        selectedType = this.dataset.type;
                    } else if (step.id === 'step2') {
                        selectedColor = this.dataset.color;
                    } else if (step.id === 'step3') {
                        selectedSize = this.dataset.size;
                    }

                    updateTshirtPreview();
                    currentStep++;
                    showStep(currentStep);
                });
            });

            imageUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        addCustomization('image', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            addTextBtn.addEventListener('click', function() {
                const text = customText.value;
                if (text) {
                    addCustomization('text', text);
                }
            });

            function addCustomization(type, content) {
                const customization = document.createElement('div');
                customization.classList.add('customization-item');
                if (type === 'image') {
                    const img = document.createElement('img');
                    img.src = content;
                    customization.appendChild(img);
                } else if (type === 'text') {
                    customization.textContent = content;
                    customization.style.color = textColor.value;
                    customization.style.fontFamily = fontFamily.value;
                }
                tshirtPreview.appendChild(customization);
                makeResizableAndDraggable(customization);
            }

            function makeResizableAndDraggable(element) {
                interact(element)
                    .draggable({
                        inertia: true,
                        modifiers: [
                            interact.modifiers.restrictRect({
                                restriction: 'parent',
                                endOnly: true
                            })
                        ],
                        autoScroll: true,
                        listeners: { move: dragMoveListener }
                    })
                    .resizable({
                        edges: { left: true, right: true, bottom: true, top: true },
                        listeners: {
                            move (event) {
                                Object.assign(event.target.style, {
                                    width: `${event.rect.width}px`,
                                    height: `${event.rect.height}px`
                                })
                            }
                        }
                    });
            }

            function dragMoveListener(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.transform = `translate(${x}px, ${y}px)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
            }

            saveDesignBtn.addEventListener('click', function() {
                const design = {
                    type: selectedType,
                    color: selectedColor,
                    size: selectedSize,
                    customizations: Array.from(tshirtPreview.querySelectorAll('.customization-item')).map(item => ({
                        type: item.querySelector('img') ? 'image' : 'text',
                        content: item.querySelector('img') ? item.querySelector('img').src : item.textContent,
                        position: {
                            x: parseFloat(item.getAttribute('data-x')) || 0,
                            y: parseFloat(item.getAttribute('data-y')) || 0
                        },
                        size: {
                            width: parseFloat(item.style.width),
                            height: parseFloat(item.style.height)
                        }
                    }))
                };
                console.log('Saved design:', design);
                alert('Design saved successfully!');
                // Here you would typically send the design data to the server
            });

            showStep(currentStep);
        });
    </script>
</body>
</html>