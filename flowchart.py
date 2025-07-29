import spacy
import os
from huggingface_hub import InferenceClient
import re
import textwrap
from collections import Counter

nlp = spacy.load("en_core_web_sm")

def enrich_technical_terms(step):
    """Adds explanatory details to technical terms in steps"""
    term_details = {
        'chlorophyll': ' (green pigment in chloroplasts)',
        'light reactions': ' (convert light to chemical energy)',
        'calvin cycle': ' (carbon fixation process)',
        'ATP': ' (energy currency of cells)',
        'CO2': ' (carbon dioxide)',
        'water molecules': ' (H₂O)'
    }
    
    for term, detail in term_details.items():
        if term in step.lower():
            step = step.replace(term, term + detail)
            break  # Only add one detail per step to avoid clutter
    
    return step

def clean_flowchart_text(text):
    """Improved text cleaning that preserves and enhances technical details"""
    text = re.sub(r'[.,;]\s*$', '', text.strip())
    text = re.sub(r'\s+', ' ', text)
    
    # Capitalize while preserving scientific notation
    if len(text) > 1 and not text[:2].isupper():
        text = text[0].upper() + text[1:]
    
    return text

def generate_detailed_steps(text):
    """Generates steps with more detailed explanations"""
    api_token = os.getenv("HF_API_TOKEN")
    client = InferenceClient(token=api_token)
    
    prompt = (
        "Convert this scientific process into 5-7 detailed steps. Each step should:\n"
        "- Be 8-25 words long\n"
        "- Start with a strong verb\n"
        "- Include key scientific terms\n"
        "- Explain one complete sub-process\n\n"
        "Example:\n"
        "'Chlorophyll molecules in thylakoid membranes absorb sunlight energy'\n\n"
        "Text: {text}\n\n"
        "Numbered steps:"
    ).format(text=text[:2000])

    try:
        response = client.text_generation(
            prompt,
            model="facebook/bart-large-cnn",
            max_new_tokens=300,
            temperature=0.6,
            do_sample=True
        )
        
        steps = []
        for line in response.split('\n'):
            if re.match(r'^\d+[.)]\s*', line):
                step = re.sub(r'^\d+[.)]\s*', '', line).strip()
                step = clean_flowchart_text(step)
                step = enrich_technical_terms(step)
                if 8 <= len(step.split()) <= 25:
                    steps.append(step)
        return steps[:7]
    
    except Exception as e:
        print(f"API error: {e}")
        return None

def generate_flowchart(text):
    """Generates flowcharts with detailed technical explanations"""
    # First try to get detailed steps
    steps = generate_detailed_steps(text)
    
    # Fallback if detailed generation fails
    if not steps or len(steps) < 3:
        doc = nlp(text)
        sentences = [sent.text.strip() for sent in doc.sents if len(sent.text.split()) >= 5]
        steps = [clean_flowchart_text(sent) for sent in sentences[:7]]
    
    # Generate nodes with adjusted layout
    nodes = []
    edges = []
    y_pos = 0
    spacing = 180  # More vertical space for detailed boxes
    
    for i, step in enumerate(steps):
        # Slightly wider text wrapping for detailed content
        wrapped = "\n".join(textwrap.wrap(step, width=32))
        height = max(70, 25 * (1 + wrapped.count('\n')))
        
        nodes.append({
            "id": i,
            "label": wrapped,
            "x": 0,
            "y": y_pos,
            "widthConstraint": {"minimum": 240, "maximum": 300},
            "heightConstraint": {"minimum": height},
            "font": {"size": 15},  # Slightly smaller font for more text
            "margin": 12  # More internal padding
        })
        
        if i > 0:
            edges.append({
                "from": i-1,
                "to": i,
                "arrows": "to",
                "smooth": {"type": "cubicBezier"}
            })
        
        y_pos += height + spacing
    
    return {
        "nodes": nodes,
        "edges": edges,
        "original_text": text
    }