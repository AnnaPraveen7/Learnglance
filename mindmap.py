import spacy
from collections import Counter
import numpy as np
import textwrap
import json
import os

FEEDBACK_FILE = "feedback.json"

def extract_topics(text):
    """Extracts a main topic, related subtopics, and hierarchical sub-subtopics."""
    nlp = spacy.load("en_core_web_sm")
    doc = nlp(text)
    
    # Logic to extract main topic (same as original)
    first_sentence = next(doc.sents)
    first_subjects = [token.lemma_.lower() for token in first_sentence if token.dep_ == "nsubj" and token.pos_ in ["NOUN", "PROPN"] and token.is_alpha and len(token.text) > 2]
    first_noun_phrases = [chunk.text.lower() for chunk in first_sentence.noun_chunks if len(chunk.text.split()) > 1 and chunk.root.pos_ in ["NOUN", "PROPN"]]
    
    if first_subjects:
        main_topic = first_subjects[0]
    elif first_noun_phrases:
        main_topic = first_noun_phrases[0]
    else:
        subjects = [token.lemma_.lower() for sent in doc.sents for token in sent if token.dep_ == "nsubj" and token.pos_ in ["NOUN", "PROPN"] and token.is_alpha and len(token.text) > 2]
        noun_phrases = [chunk.text.lower() for sent in doc.sents for chunk in sent.noun_chunks if len(chunk.text.split()) > 1 and chunk.root.pos_ in ["NOUN", "PROPN"]]
        main_topic = Counter(noun_phrases).most_common(1)[0][0] if noun_phrases else (Counter(subjects).most_common(1)[0][0] if subjects else "Main Concept")
    
    main_topic = main_topic.title()
    all_nouns = [token.lemma_.lower() for token in doc if token.pos_ in ["NOUN", "PROPN"] and token.is_alpha and len(token.text) > 2]
    primary_subtopics = {}
    used_sentences = set()
    
    for sent in doc.sents:
        if any(main_topic.lower() in token.text.lower() for token in sent):
            for chunk in sent.noun_chunks:
                subtopic = chunk.text.lower()
                if subtopic != main_topic.lower() and 1 <= len(subtopic.split()) <= 2 and any(noun in all_nouns for noun in subtopic.split()) and len(subtopic) > 2:
                    if subtopic not in primary_subtopics:
                        related_sentences = [s.text for s in doc.sents if subtopic in s.text.lower() and s.text not in used_sentences]
                        if related_sentences:
                            primary_subtopics[subtopic] = {"description": related_sentences[0], "subtopics": {}}
                            used_sentences.add(related_sentences[0])
    
    if len(primary_subtopics) < 6:
        for chunk in doc.noun_chunks:
            phrase = chunk.text.lower()
            if phrase not in primary_subtopics and phrase.lower() not in main_topic.lower() and 1 <= len(phrase.split()) <= 2:
                related_sentences = [s.text for s in doc.sents if phrase in s.text.lower() and s.text not in used_sentences]
                if related_sentences:
                    primary_subtopics[phrase] = {"description": related_sentences[0], "subtopics": {}}
                    used_sentences.add(related_sentences[0])
    
    if len(primary_subtopics) > 6:
        primary_subtopics = dict(list(primary_subtopics.items())[:6])
    
    for subtopic, data in primary_subtopics.items():
        for sent in doc.sents:
            if any(subtopic in token.text.lower() for token in sent):
                for chunk in sent.noun_chunks:
                    sub_subtopic = chunk.text.lower()
                    if sub_subtopic != subtopic and sub_subtopic not in main_topic.lower() and 1 <= len(sub_subtopic.split()) <= 2 and any(noun in all_nouns for noun in sub_subtopic.split()) and len(sub_subtopic) > 2:
                        if sub_subtopic not in data["subtopics"]:
                            related_sentences = [s.text for s in doc.sents if sub_subtopic in s.text.lower() and s.text not in used_sentences]
                            if related_sentences:
                                data["subtopics"][sub_subtopic] = related_sentences[0]
                                used_sentences.add(related_sentences[0])
    
    return main_topic, primary_subtopics, {}

def generate_mindmap(text):
    """Generates a mind map structure as a JSON object."""
    main_topic, primary_subtopics, _ = extract_topics(text)
    nodes = [{"id": 0, "label": "\n".join(textwrap.wrap(main_topic, width=15)), "group": "main", "x": 0, "y": 0}]
    edges = []
    node_id = 1
    primary_radius = 250
    primary_angles = np.linspace(0, 2 * np.pi, len(primary_subtopics), endpoint=False)
    primary_positions = [(primary_radius * np.cos(angle), primary_radius * np.sin(angle)) for angle in primary_angles]
    
    for i, (subtopic, data) in enumerate(primary_subtopics.items()):
        x, y = primary_positions[i]
        primary_node_id = node_id
        nodes.append({"id": node_id, "label": subtopic.capitalize(), "group": "primary", "x": x, "y": y})
        edges.append({"from": 0, "to": primary_node_id})
        node_id += 1
        
        desc_x, desc_y = (450 * np.cos(primary_angles[i]), 450 * np.sin(primary_angles[i]))
        nodes.append({"id": node_id, "label": "\n".join(textwrap.wrap(data["description"], width=25)), "group": "description", "x": desc_x, "y": desc_y})
        edges.append({"from": primary_node_id, "to": node_id})
        node_id += 1
        
        secondary_radius = 350
        secondary_angles = np.linspace(primary_angles[i] - np.pi / 6, primary_angles[i] + np.pi / 6, len(data["subtopics"]), endpoint=True)
        for j, (sub_subtopic, sub_description) in enumerate(data["subtopics"].items()):
            sec_x, sec_y = (secondary_radius * np.cos(secondary_angles[j]), secondary_radius * np.sin(secondary_angles[j]))
            nodes.append({"id": node_id, "label": sub_subtopic.capitalize(), "group": "secondary", "x": sec_x, "y": sec_y})
            edges.append({"from": primary_node_id, "to": node_id})
            node_id += 1
            
            sub_desc_x, sub_desc_y = (550 * np.cos(secondary_angles[j]), 550 * np.sin(secondary_angles[j]))
            nodes.append({"id": node_id, "label": "\n".join(textwrap.wrap(sub_description, width=25)), "group": "sub_description", "x": sub_desc_x, "y": sub_desc_y})
            edges.append({"from": node_id - 1, "to": node_id})
            node_id += 1
    
    return {"nodes": nodes, "edges": edges, "original_text": text}

def save_user_feedback(original_data, edited_data):
    """Save the original and edited mind map data for learning."""
    feedback_entry = {"original": original_data, "edited": edited_data}
    feedback_data = json.load(open(FEEDBACK_FILE, "r")) if os.path.exists(FEEDBACK_FILE) else []
    feedback_data.append(feedback_entry)
    with open(FEEDBACK_FILE, "w") as f:
        json.dump(feedback_data, f, indent=4)