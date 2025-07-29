<?php
header("Content-Type: text/html; charset=UTF-8");
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: student_login.php');
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['student_id'];

include 'db_connection.php';

$query = "SELECT name, class FROM student_details WHERE student_id = :student_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['student_id' => $user_id]);
$student_details = $stmt->fetch();

if ($student_details) {
    $student_name = $student_details['name'];
    $student_class = $student_details['class'];
} else {
    $student_name = 'Unknown Student';
    $student_class = 'N/A';
}

$query = "SELECT name FROM student_details WHERE class = :student_class AND student_id != :student_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['student_class' => $student_class, 'student_id' => $user_id]);
$other_students = $stmt->fetchAll();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: student_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind Map and Flowchart Generator</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="st.css">
</head>
<body>
<header>
    <a href="#" class="logo">BLUESHIPIN</a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#service">Service</a>
        <a href="#help">Help</a>
    </nav>
    <div class="auth-buttons">
        <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
        <a href="index.php?logout=true" class="btn sign-out">Sign Out</a>
    </div>
</header>
    <div class="app-container">
        <div class="main-panel">
            <h2>Mind Map and Flowchart Generator</h2>
            <p class="description">Enter any text to generate a mind map, flowchart, summary, or image. You can edit the mind map and flowchart by dragging nodes, double-clicking to edit text, or right-clicking to add/remove nodes.</p>
            <textarea id="userInput" rows="6" cols="50" placeholder="Paste your text here..."></textarea>
            <div class="button-group">
                <button id="generateMindmapButton" onclick="generateMindMap(false, false)"><i class="fas fa-project-diagram"></i> Generate Mind Map</button>
                <button id="generateFlowchartButton" onclick="generateMindMap(false, true)"><i class="fas fa-stream"></i> Generate Flowchart</button>
                <button id="generateSummaryButton" onclick="generateMindMap(true, false)"><i class="fas fa-file-alt"></i> Generate Summary</button>
                <button id="generateImageButton" onclick="generateImage()"><i class="fas fa-image"></i> Generate Image</button>
                <button id="saveButton" onclick="saveContent()" style="display: none;"><i class="fas fa-save"></i> Save and Submit</button>
            </div>
            <div class="loading" id="loadingIndicator">
                <div class="loading-spinner"></div>
                <p>Generating your content...</p>
            </div>
            <div class="result-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="switchTab('mindmap')">Mind Map</button>
                    <button class="tab-button" onclick="switchTab('flowchart')">Flowchart</button>
                    <button class="tab-button" onclick="switchTab('image')">Image</button>
                </div>
                <div class="tab-content active" id="mindmapTab"><div id="mindmapContainer"></div></div>
                <div class="tab-content" id="flowchartTab"><div id="flowchartContainer"></div></div>
                <div class="tab-content" id="imageTab">
                    <div id="imageSelectionContainer" style="display: flex; flex-wrap: wrap; gap: 10px; padding: 10px;"></div>
                </div>
            </div>
        </div>
        <div class="preview-panel">
            <h3>Preview</h3>
            <div class="preview-section"><h4>Summary</h4><div id="summaryText"></div></div>
            <div class="preview-section"><h4>Mind Map Image</h4><div class="preview-image-container"><img id="previewImage" src="" alt="Mindmap Preview"><button class="remove-image-btn" onclick="removeImage('previewImage')"><i class="fas fa-times"></i></button></div></div>
            <div class="preview-section"><h4>Flowchart Image</h4><div class="preview-image-container"><img id="flowchartPreviewImage" src="" alt="Flowchart Preview"><button class="remove-image-btn" onclick="removeImage('flowchartPreviewImage')"><i class="fas fa-times"></i></button></div></div>
            <div class="preview-section"><h4>Generated Images</h4>
                <div id="generatedImagePreviewContainer">
                    <div class="preview-image-container"><img id="generatedImagePreview1" src="" alt="Generated Image 1"><button class="remove-image-btn" onclick="removeImage('generatedImagePreview1')"><i class="fas fa-times"></i></button></div>
                    <div class="preview-image-container"><img id="generatedImagePreview2" src="" alt="Generated Image 2"><button class="remove-image-btn" onclick="removeImage('generatedImagePreview2')"><i class="fas fa-times"></i></button></div>
                    <div class="preview-image-container"><img id="generatedImagePreview3" src="" alt="Generated Image 3"><button class="remove-image-btn" onclick="removeImage('generatedImagePreview3')"><i class="fas fa-times"></i></button></div>
                </div>
            </div>
        </div>
    </div>
    <button id="exportPdfBtn" title="Export to PDF" onclick="exportAsPDF()" style="display: none;"><i class="fas fa-file-pdf"></i></button>

    <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        let mindmapNetwork = null, flowchartNetwork = null, mindmapData = null, flowchartData = null, mindmapId = null, savedContent = false;
        let generatedImageUrls = [];
        let savedImageUrls = [];
        let mindmapPreviewSrc = "";
        let flowchartPreviewSrc = "";
        let lastSavedMindmap = null;
        let lastSavedFlowchart = null;

        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => button.addEventListener('click', function() { this.style.transform = 'scale(0.98)'; setTimeout(() => this.style.transform = '', 100); }));
            const textarea = document.getElementById('userInput');
            textarea.addEventListener('input', function() { this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px'; });
            setInterval(updatePdfButtonVisibility, 500);
        });

        function updatePdfButtonVisibility() {
            const pdfBtn = document.getElementById('exportPdfBtn');
            pdfBtn.style.display = (document.getElementById('previewImage').style.display !== "none" || 
                                   document.getElementById('flowchartPreviewImage').style.display !== "none" || 
                                   [1, 2, 3].some(i => document.getElementById(`generatedImagePreview${i}`).style.display !== "none") || 
                                   document.getElementById('summaryText').style.display !== "none") ? 'flex' : 'none';
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
            document.getElementById(tabName + 'Tab').classList.add('active');
            document.querySelector(`.tab-button[onclick="switchTab('${tabName}')"]`).classList.add('active');
            if (tabName === 'image' && generatedImageUrls.length > 0) {
                displayImageSelection();
            }
        }

        function generateMindMap(includeSummary, includeFlowchart) {
            const text = document.getElementById("userInput").value;
            if (!text.trim()) { alert("Please enter some text first!"); return; }
            savedContent = false;
            document.getElementById("loadingIndicator").style.display = "block";
            ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = true);
            document.getElementById("saveButton").style.display = "none";
            document.getElementById("mindmapContainer").style.display = "none";
            document.getElementById("flowchartContainer").style.display = "none";
            document.getElementById("imageSelectionContainer").innerHTML = "";

            fetch("http://127.0.0.1:5000/generate_mindmap", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ text, include_summary: includeSummary, include_flowchart: includeFlowchart })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("loadingIndicator").style.display = "none";
                ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = false);
                if (data.error) { alert("Error: " + data.error); return; }
                
                mindmapData = data;
                mindmapId = data.mindmap_id;
                flowchartData = data.flowchart;
                
                const summaryText = document.getElementById("summaryText");
                if (includeSummary && data.summary) {
                    summaryText.innerText = data.summary;
                    summaryText.style.display = "block";
                } else if (!includeSummary && summaryText.innerText.trim() === "") {
                    summaryText.style.display = "none";
                }
                
                if (!includeSummary && !includeFlowchart && data.nodes && data.edges) {
                    document.getElementById("mindmapContainer").style.display = "block";
                    document.getElementById("saveButton").style.display = "flex";
                    renderMindMap(data);
                    switchTab('mindmap');
                }
                
                if (includeFlowchart && data.flowchart && data.flowchart.nodes && data.flowchart.edges) {
                    document.getElementById("flowchartContainer").style.display = "block";
                    document.getElementById("saveButton").style.display = "flex";
                    renderFlowchart(data.flowchart);
                    switchTab('flowchart');
                }
            })
            .catch(error => {
                document.getElementById("loadingIndicator").style.display = "none";
                ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = false);
                console.error("Error:", error);
                alert("Error connecting to the server. Please ensure the Flask server is running.");
            });
        }

        function generateImage() {
            const text = document.getElementById("userInput").value;
            if (!text.trim()) { alert("Please enter some text first!"); return; }
            document.getElementById("loadingIndicator").style.display = "block";
            ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = true);

            fetch("http://127.0.0.1:5000/generate_image", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ text })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("loadingIndicator").style.display = "none";
                ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = false);
                if (data.error) { alert("Error: " + data.error); return; }
                
                generatedImageUrls = data.image_urls.filter(url => url);
                if (generatedImageUrls.length > 0) {
                    switchTab('image');
                    displayImageSelection();
                    document.getElementById("saveButton").style.display = "flex";
                } else {
                    alert("No images generated.");
                }
            })
            .catch(error => {
                document.getElementById("loadingIndicator").style.display = "none";
                ["generateMindmapButton", "generateFlowchartButton", "generateSummaryButton", "generateImageButton"].forEach(id => document.getElementById(id).disabled = false);
                console.error("Error:", error);
                alert("Error fetching images. Please check your API credentials and server status.");
            });
        }

        function displayImageSelection() {
            const container = document.getElementById("imageSelectionContainer");
            container.innerHTML = "";
            generatedImageUrls.forEach((url, index) => {
                const imgContainer = document.createElement("div");
                imgContainer.style.position = "relative";
                imgContainer.style.margin = "5px";
                imgContainer.style.border = savedImageUrls.includes(url) ? "2px solid #4361ee" : "none";
                
                const img = document.createElement("img");
                img.src = url;
                img.style.width = "200px";
                img.style.height = "auto";
                img.style.cursor = "pointer";
                img.onclick = () => toggleImageSelection(url);
                imgContainer.appendChild(img);
                
                container.appendChild(imgContainer);
            });
        }

        function toggleImageSelection(url) {
            const index = savedImageUrls.indexOf(url);
            if (index === -1) {
                if (savedImageUrls.length < 3) {
                    savedImageUrls.push(url);
                } else {
                    alert("You can only save up to 3 images.");
                    return;
                }
            } else {
                savedImageUrls.splice(index, 1);
            }
            displayImageSelection();
        }

        function renderMindMap(data) {
            if (mindmapNetwork) mindmapNetwork.destroy();
            const nodes = new vis.DataSet(data.nodes.map(node => ({
                ...node,
                shape: node.group === "main" ? "ellipse" : "box",
                color: { background: node.group === "main" ? "#87CEEB" : node.group === "primary" ? "#90EE90" : node.group === "secondary" ? "#E0FFFF" : node.group === "description" ? "#FFFACD" : "#D3D3D3", border: "black" },
                font: { size: node.group === "main" ? 28 : 20, multi: "html", face: "Poppins, Arial, sans-serif", strokeWidth: 2, strokeColor: "rgba(255,255,255,0.7)" }
            })));
            const edges = new vis.DataSet(data.edges.map(edge => ({ ...edge, arrows: "to", width: 2 })));
            mindmapNetwork = new vis.Network(document.getElementById("mindmapContainer"), { nodes, edges }, {
                interaction: { dragNodes: true, dragView: true, zoomView: true, hover: true },
                manipulation: {
                    enabled: true, initiallyActive: true,
                    addNode: (nodeData, callback) => { nodeData.label = "New Node"; nodeData.group = "secondary"; callback(nodeData); },
                    editNode: (nodeData, callback) => { const newLabel = prompt("Enter new label:", nodeData.label); if (newLabel) nodeData.label = newLabel; callback(nodeData); },
                    addEdge: (edgeData, callback) => callback(edgeData),
                    deleteNode: true, deleteEdge: true
                },
                physics: { enabled: false }
            });
        }

        function renderFlowchart(data) {
            if (flowchartNetwork) flowchartNetwork.destroy();
            const nodes = new vis.DataSet(data.nodes.map(node => ({
                ...node,
                shape: "box",
                color: { background: "#FFDAB9", border: "black" },
                font: { size: 28, multi: "html", color: "black", face: "Poppins, Arial, sans-serif" }, // Increased from 20 to 28 for much clearer text
                widthConstraint: { minimum: 250, maximum: 300 }, // Increased from 200/250 to 250/300 to fit larger text
                heightConstraint: { minimum: 80, maximum: 120 }, // Increased from 60/100 to 80/120 for better readability
                margin: 15 // Increased from 10 to 15 for padding around larger text
            })));
            const edges = new vis.DataSet(data.edges.map(edge => ({ ...edge, arrows: "to", color: "black", width: 1, smooth: { type: "cubicBezier", roundness: 0.4 } })));
            flowchartNetwork = new vis.Network(document.getElementById("flowchartContainer"), { nodes, edges }, {
                interaction: { dragNodes: true, dragView: true, zoomView: true, hover: true },
                manipulation: {
                    enabled: true, initiallyActive: true,
                    addNode: (nodeData, callback) => { nodeData.label = "New Step"; nodeData.group = "flowchart_step"; callback(nodeData); },
                    editNode: (nodeData, callback) => { const newLabel = prompt("Enter new label:", nodeData.label); if (newLabel) nodeData.label = newLabel; callback(nodeData); },
                    addEdge: (edgeData, callback) => callback(edgeData),
                    deleteNode: true, deleteEdge: true
                },
                physics: { enabled: false }, // Disabled physics to allow free dragging in all directions
                layout: { 
                    hierarchical: { 
                        enabled: true, 
                        direction: "UD", 
                        sortMethod: "directed", 
                        nodeSpacing: 300, // Increased from 250 for better spacing with larger nodes
                        levelSeparation: 250, // Increased from 200 for more vertical space
                        edgeMinimization: true, 
                        blockShifting: true, 
                        parentCentralization: true 
                    } 
                },
                edges: { smooth: { type: "cubicBezier", forceDirection: "vertical" } }
            });

            // Disable hierarchical layout after initial render to allow free movement
            setTimeout(() => {
                flowchartNetwork.setOptions({ 
                    layout: { hierarchical: { enabled: false } }, 
                    physics: { enabled: false } 
                });
                flowchartNetwork.fit({ animation: { duration: 600, easingFunction: 'easeInOutQuad' } });
            }, 150);
        }

        async function saveContent() {
            let editedMindmap = null, editedFlowchart = null;
            let hasContentToSave = false;

            if (mindmapNetwork && mindmapData && mindmapData.nodes) {
                const nodes = mindmapNetwork.body.data.nodes.get();
                const edges = mindmapNetwork.body.data.edges.get();
                editedMindmap = { 
                    nodes: nodes.map(node => ({ 
                        id: node.id, 
                        label: node.label, 
                        group: node.group, 
                        x: mindmapNetwork.getPositions([node.id])[node.id].x, 
                        y: mindmapNetwork.getPositions([node.id])[node.id].y 
                    })), 
                    edges: edges.map(edge => ({ from: edge.from, to: edge.to })) 
                };
                const mindmapChanged = !lastSavedMindmap || JSON.stringify(editedMindmap) !== JSON.stringify(lastSavedMindmap);
                if (!mindmapPreviewSrc || mindmapChanged) {
                    const canvas = await html2canvas(document.getElementById("mindmapContainer"), { 
                        scale: 4, 
                        dpi: 300, 
                        logging: false, 
                        useCORS: true 
                    });
                    mindmapPreviewSrc = canvas.toDataURL("image/png");
                    document.getElementById("previewImage").src = mindmapPreviewSrc;
                    document.getElementById("previewImage").style.display = "block";
                    hasContentToSave = true;
                } else {
                    document.getElementById("previewImage").src = mindmapPreviewSrc;
                    document.getElementById("previewImage").style.display = "block";
                }
            }

            if (flowchartNetwork && flowchartData && flowchartData.nodes) {
                const nodes = flowchartNetwork.body.data.nodes.get();
                const edges = flowchartNetwork.body.data.edges.get();
                editedFlowchart = { 
                    nodes: nodes.map(node => ({ 
                        id: node.id, 
                        label: node.label, 
                        group: node.group, 
                        x: flowchartNetwork.getPositions([node.id])[node.id].x, 
                        y: flowchartNetwork.getPositions([node.id])[node.id].y 
                    })), 
                    edges: edges.map(edge => ({ from: edge.from, to: edge.to })) 
                };
                const flowchartChanged = !lastSavedFlowchart || JSON.stringify(editedFlowchart) !== JSON.stringify(lastSavedFlowchart);
                if (!flowchartPreviewSrc || flowchartChanged) {
                    const canvas = await html2canvas(document.getElementById("flowchartContainer"), { 
                        scale: 4, 
                        dpi: 300, 
                        logging: false, 
                        useCORS: true 
                    });
                    flowchartPreviewSrc = canvas.toDataURL("image/png");
                    document.getElementById("flowchartPreviewImage").src = flowchartPreviewSrc;
                    document.getElementById("flowchartPreviewImage").style.display = "block";
                    hasContentToSave = true;
                } else {
                    document.getElementById("flowchartPreviewImage").src = flowchartPreviewSrc;
                    document.getElementById("flowchartPreviewImage").style.display = "block";
                }
            }

            if (savedImageUrls.length > 0) {
                updateGeneratedImagePreviews();
                hasContentToSave = true;
            }

            if (!mindmapNetwork && !flowchartNetwork && savedImageUrls.length === 0) {
                alert("No content to save! Please generate a mindmap, flowchart, or select an image first.");
                return;
            }

            if (hasContentToSave) {
                if (editedMindmap || editedFlowchart) {
                    fetch("http://127.0.0.1:5000/save_feedback", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ 
                            edited_mindmap: editedMindmap || {}, 
                            edited_flowchart: editedFlowchart || {}, 
                            mindmap_id: mindmapId 
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) { 
                            alert("Content saved successfully!");
                            lastSavedMindmap = editedMindmap;
                            lastSavedFlowchart = editedFlowchart;
                            savedContent = true;
                            updatePdfButtonVisibility();
                        } else { 
                            console.error("Error saving content: " + (data.error || "Unknown error")); 
                        }
                    })
                    .catch(error => { 
                        console.error("Error:", error); 
                    });
                } else {
                    alert("Images saved successfully!");
                    savedContent = true;
                    updatePdfButtonVisibility();
                }
            } else {
                alert("No new changes to save. Edit the mindmap/flowchart or select new images to save again.");
                updatePdfButtonVisibility();
            }
        }

        function removeImage(imageId) {
            const img = document.getElementById(imageId);
            if (img) {
                img.src = "";
                img.style.display = "none";
                if (imageId === "previewImage") {
                    mindmapPreviewSrc = "";
                    lastSavedMindmap = null;
                } else if (imageId === "flowchartPreviewImage") {
                    flowchartPreviewSrc = "";
                    lastSavedFlowchart = null;
                } else if (imageId.startsWith("generatedImagePreview")) {
                    const index = parseInt(imageId.replace("generatedImagePreview", "")) - 1;
                    if (index >= 0 && index < savedImageUrls.length) {
                        savedImageUrls.splice(index, 1);
                        updateGeneratedImagePreviews();
                    }
                }
                updatePdfButtonVisibility();
            }
        }

        function updateGeneratedImagePreviews() {
            [1, 2, 3].forEach(i => {
                const img = document.getElementById(`generatedImagePreview${i}`);
                if (i <= savedImageUrls.length) {
                    img.src = savedImageUrls[i - 1];
                    img.style.display = "block";
                } else {
                    img.src = "";
                    img.style.display = "none";
                }
            });
        }

        async function exportAsPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            let yPos = 20;
            let hasContent = false;

            doc.setFontSize(20);
            doc.text("Generated Content", 105, yPos, { align: 'center' });
            yPos += 25;

            // Add Summary
            const summary = document.getElementById('summaryText');
            if (summary.style.display !== "none" && summary.innerText.trim()) {
                doc.setFontSize(12);
                doc.text("Summary:", 14, yPos);
                yPos += 10;
                const splitSummary = doc.splitTextToSize(summary.innerText, 180);
                doc.text(splitSummary, 14, yPos);
                yPos += splitSummary.length * 7 + 15;
                hasContent = true;
            }

            // Add Mind Map
            const mindmapImg = document.getElementById('previewImage');
            if (mindmapImg.style.display !== "none" && mindmapImg.src && mindmapImg.src !== window.location.href) {
                doc.setFontSize(12);
                const imgWidth = 180;
                const imgHeight = (mindmapImg.naturalHeight * imgWidth) / mindmapImg.naturalWidth;
                if (yPos + imgHeight + 20 > 280) {
                    doc.addPage();
                    yPos = 20;
                }
                doc.text("Mind Map:", 14, yPos);
                yPos += 10;
                doc.addImage(mindmapImg.src, 'PNG', 15, yPos, imgWidth, imgHeight);
                yPos += imgHeight + 15;
                hasContent = true;
            }

            // Add Flowchart
            const flowchartImg = document.getElementById('flowchartPreviewImage');
            if (flowchartImg.style.display !== "none" && flowchartImg.src && flowchartImg.src !== window.location.href) {
                doc.setFontSize(12);
                const imgWidth = 180;
                const imgHeight = (flowchartImg.naturalHeight * imgWidth) / flowchartImg.naturalWidth;
                if (yPos + imgHeight + 20 > 280) {
                    doc.addPage();
                    yPos = 20;
                }
                doc.text("Flowchart:", 14, yPos);
                yPos += 10;
                doc.addImage(flowchartImg.src, 'PNG', 15, yPos, imgWidth, imgHeight);
                yPos += imgHeight + 15;
                hasContent = true;
            }

            // Add Saved Images
            for (let i = 0; i < savedImageUrls.length; i++) {
                const url = savedImageUrls[i];
                console.log(`Processing Generated Image ${i + 1}: ${url}`);
                if (url) {
                    try {
                        const response = await fetch(url, { mode: 'cors' });
                        if (!response.ok) {
                            console.warn(`Fetch failed for ${url}: ${response.status} ${response.statusText}`);
                            throw new Error("Fetch failed");
                        }
                        const blob = await response.blob();
                        const imgData = await new Promise((resolve) => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result);
                            reader.readAsDataURL(blob);
                        });

                        const img = new Image();
                        img.src = imgData;
                        await new Promise((resolve, reject) => {
                            img.onload = () => {
                                console.log(`Image ${i + 1} loaded successfully: ${url}`);
                                resolve();
                            };
                            img.onerror = () => {
                                console.warn(`Image load failed for ${url}`);
                                resolve();
                            };
                            setTimeout(() => reject(new Error("Image load timeout")), 10000);
                        });

                        const imgWidth = 180;
                        const imgHeight = (img.naturalHeight || 100) * imgWidth / (img.naturalWidth || 100);
                        if (yPos + imgHeight + 20 > 280) {
                            doc.addPage();
                            yPos = 20;
                        }
                        doc.setFontSize(12);
                        doc.text(`Generated Image ${i + 1}:`, 14, yPos);
                        yPos += 10;
                        doc.addImage(imgData, 'PNG', 15, yPos, imgWidth, imgHeight);
                        yPos += imgHeight + 15;
                        hasContent = true;
                    } catch (e) {
                        console.error(`Error processing ${url}:`, e);
                        const previewImg = document.getElementById(`generatedImagePreview${i + 1}`);
                        if (previewImg && previewImg.src && previewImg.style.display !== "none" && previewImg.src !== window.location.href) {
                            console.log(`Falling back to preview image for Generated Image ${i + 1}: ${previewImg.src}`);
                            try {
                                const imgWidth = 180;
                                const imgHeight = (previewImg.naturalHeight * imgWidth) / previewImg.naturalWidth;
                                if (yPos + imgHeight + 20 > 280) {
                                    doc.addPage();
                                    yPos = 20;
                                }
                                doc.setFontSize(12);
                                doc.text(`Generated Image ${i + 1}:`, 14, yPos);
                                yPos += 10;
                                doc.addImage(previewImg.src, 'PNG', 15, yPos, imgWidth, imgHeight);
                                yPos += imgHeight + 15;
                                hasContent = true;
                            } catch (fallbackError) {
                                console.error(`Fallback failed for Generated Image ${i + 1}:`, fallbackError);
                                doc.setFontSize(10);
                                doc.text(`Failed to load Generated Image ${i + 1}`, 14, yPos);
                                yPos += 10;
                            }
                        } else {
                            doc.setFontSize(10);
                            doc.text(`Failed to load Generated Image ${i + 1}`, 14, yPos);
                            yPos += 10;
                        }
                    }
                }
            }

            if (!hasContent) {
                doc.setFontSize(12);
                doc.text("No content available to export.", 14, yPos);
            } else {
                doc.save("generated_content.pdf");
            }
        }
    </script>
</body>
</html>