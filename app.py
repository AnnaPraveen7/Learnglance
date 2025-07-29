from flask import Flask, request, jsonify, send_from_directory
from flask_cors import CORS
import os
import uuid
from mindmap import generate_mindmap, save_user_feedback
from flowchart import generate_flowchart
from summary import generate_summary
from image_search import search_image

app = Flask(__name__)
CORS(app)

STATIC_FOLDER = "static"
if not os.path.exists(STATIC_FOLDER):
    os.makedirs(STATIC_FOLDER)

original_mindmap_data = {}

GOOGLE_API_KEY = "AIzaSyAp6LR4Ua3Ak300sqmgEcP1W4EbYLOEh3U"
GOOGLE_CSE_ID = "865d06a7fbab842dd"

@app.route("/generate_mindmap", methods=["POST"])
def generate():
    data = request.json
    text = data.get("text", "").strip()
    include_summary = data.get("include_summary", False)
    include_flowchart = data.get("include_flowchart", False)

    if not text:
        return jsonify({"error": "No text provided"}), 400

    try:
        mindmap_data = generate_mindmap(text)
        result = {"nodes": mindmap_data["nodes"], "edges": mindmap_data["edges"]}
        
        if include_summary:
            result["summary"] = generate_summary(text)
        
        if include_flowchart:
            flowchart_data = generate_flowchart(text)
            result["flowchart"] = {"nodes": flowchart_data["nodes"], "edges": flowchart_data["edges"]}
        
        mindmap_id = str(uuid.uuid4())
        original_mindmap_data[mindmap_id] = result
        return jsonify({"mindmap_id": mindmap_id, **result})
    except Exception as e:
        return jsonify({"error": f"Failed to generate content: {str(e)}"}), 500

@app.route("/generate_image", methods=["POST"])
def generate_image():
    data = request.json
    text = data.get("text", "").strip()

    if not text:
        return jsonify({"error": "No text provided"}), 400

    try:
        image_urls = search_image(text, GOOGLE_API_KEY, GOOGLE_CSE_ID, num_images=3)
        if any(url is not None for url in image_urls):
            return jsonify({"image_urls": image_urls[:3]})
        else:
            return jsonify({"error": "No images found"}), 404
    except Exception as e:
        return jsonify({"error": f"Failed to generate image: {str(e)}"}), 500

@app.route("/save_feedback", methods=["POST"])
def save_feedback():
    data = request.json
    edited_data = data.get("edited_mindmap", {})
    edited_flowchart = data.get("edited_flowchart", {})
    mindmap_id = data.get("mindmap_id", "")

    if not mindmap_id:
        return jsonify({"error": "Missing mindmap ID"}), 400

    if not edited_data and not edited_flowchart:
        return jsonify({"error": "Missing edited data"}), 400

    try:
        original_data = original_mindmap_data.get(mindmap_id, {})
        if not original_data:
            return jsonify({"error": "Original data not found"}), 404
        
        if edited_data:
            save_user_feedback(original_data, edited_data)
        
        if edited_flowchart:
            save_user_feedback(original_data.get("flowchart", {}), edited_flowchart)
        
        del original_mindmap_data[mindmap_id]
        return jsonify({"message": "Feedback saved successfully"})
    except Exception as e:
        return jsonify({"error": f"Failed to save feedback: {str(e)}"}), 500

@app.route("/static/<filename>")
def serve_image(filename):
    return send_from_directory(STATIC_FOLDER, filename)

if __name__ == "__main__":
    app.run(debug=True)