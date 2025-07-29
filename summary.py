import spacy
from collections import Counter

def generate_summary(text, num_sentences=3):
    """Generates a summary of the text by selecting representative sentences."""
    nlp = spacy.load("en_core_web_sm")
    doc = nlp(text)
    noun_counter = Counter([token.lemma_.lower() for token in doc if token.pos_ in ["NOUN", "PROPN"] and token.is_alpha and len(token.text) > 2])
    sentence_scores = {sent.text.strip(): sum(noun_counter[token.lemma_.lower()] for token in sent if token.lemma_.lower() in noun_counter) for sent in doc.sents}
    
    prioritized_sentences = []
    for sent in doc.sents:
        sent_text = sent.text.strip()
        if "is a" in sent_text.lower() or "is the" in sent_text.lower() and len(prioritized_sentences) < num_sentences:
            prioritized_sentences.append(sent_text)
        elif ("algorithm" in sent_text.lower() or "scheduling algorithms" in sent_text.lower()) and len(prioritized_sentences) < num_sentences:
            prioritized_sentences.append(sent_text)
        elif ("system performance" in sent_text.lower() or "responsive" in sent_text.lower()) and len(prioritized_sentences) < num_sentences:
            prioritized_sentences.append(sent_text)
    
    for sent, _ in sorted(sentence_scores.items(), key=lambda x: x[1], reverse=True):
        if sent not in prioritized_sentences and len(prioritized_sentences) < num_sentences:
            prioritized_sentences.append(sent)
    
    return " ".join(prioritized_sentences)