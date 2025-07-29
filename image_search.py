import requests
import re
from collections import Counter
import spacy
import json
import random

nlp = spacy.load("en_core_web_sm")

def extract_key_terms(text):
    """Extracts context-specific key terms using SpaCy for better relevance."""
    doc = nlp(text)
    
    key_terms = []
    for token in doc:
        if token.pos_ in ["NOUN", "PROPN"] and token.is_alpha and len(token.text) > 3:
            key_terms.append(token.lemma_.lower())
    
    noun_chunks = [chunk.text.lower() for chunk in doc.noun_chunks if len(chunk.text.split()) > 1]
    key_terms.extend(noun_chunks)
    
    word_freq = Counter(key_terms)
    common_words = {'a', 'an', 'the', 'and', 'or', 'is', 'are', 'was', 'were', 'be', 'in', 'of', 'to'}
    key_terms = [term for term in word_freq.keys() if word_freq[term] > 1 and term not in common_words]
    
    return key_terms[:3]

def search_image(text, api_key, cse_id, num_images=3, exclude_urls=None):
    """Searches for multiple relevant images using Google Custom Search API."""
    try:
        key_terms = extract_key_terms(text)
        doc = nlp(text)
        context_terms = ["operating system", "CPU scheduling", "algorithm diagram", "process scheduling"]
        for chunk in doc.noun_chunks:
            phrase = chunk.text.lower()
            if any(keyword in phrase for keyword in ["scheduling", "process", "CPU", "algorithm"]) and len(phrase.split()) > 1:
                context_terms.append(phrase)
        key_terms.extend(context_terms[:2])

        if not key_terms:
            query = text[:100]
        else:
            query = " ".join(sorted(set(key_terms), key=key_terms.index)[:3]) + " diagram illustration"
            if any(term in ["scheduling", "CPU", "algorithm", "process"] for term in key_terms):
                query += " operating system"

        random_terms = ["flowchart", "graph", "illustration", "diagram"]
        query += " " + random.choice(random_terms)

        url = "https://www.googleapis.com/customsearch/v1"
        params = {
            "q": query,
            "cx": cse_id,
            "key": api_key,
            "searchType": "image",
            "num": min(num_images * 2, 10),
            "start": random.randint(1, 5),
        }
        
        response = requests.get(url, params=params)
        response.raise_for_status()
        
        results = response.json()
        if "items" in results and len(results["items"]) > 0:
            image_urls = [item["link"] for item in results["items"] if "link" in item and any(ext in item["link"].lower() for ext in [".png", ".jpg", ".jpeg"])]
            if exclude_urls:
                image_urls = [url for url in image_urls if url not in exclude_urls]
            return image_urls[:num_images] if image_urls else [None]
        else:
            print(f"No items found in response: {json.dumps(results, indent=2)}")
            return [None]
    except requests.exceptions.HTTPError as e:
        error_detail = e.response.json() if e.response else str(e)
        print(f"HTTP Error: {e.response.status_code} - {error_detail}")
        return [None]
    except Exception as e:
        print(f"Image search error: {str(e)}")
        return [None]

if __name__ == "__main__":
    sample_text = "Process scheduling is a critical function of an operating system that manages the execution of multiple processes competing for the CPU."
    api_key = "AIzaSyAp6LR4Ua3Ak300sqmgEcP1W4EbYLOEh3U"
    cse_id = "865d06a7fbab842dd"
    images = search_image(sample_text, api_key, cse_id)
    print(images)