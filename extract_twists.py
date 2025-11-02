#!/usr/bin/env python3
# extract_twists.py
import requests, time, json, logging, re, html
from bs4 import BeautifulSoup, NavigableString, Tag

logging.basicConfig(level=logging.INFO, format="%(levelname)s: %(message)s")

URL = "https://wahapedia.ru/wh40k10ed/the-rules/chapter-approved-2025-26/#Twist-Deck"
HEADERS = {
    "User-Agent": "Mozilla/5.0 (compatible; twist-extractor/1.0; +https://example.com)",
    "Accept-Language": "en-US,en;q=0.9"
}
REQUEST_TIMEOUT = 10
SLEEP_BETWEEN_REQUESTS = 1.0

def get_page(url):
    for attempt in range(3):
        try:
            logging.info(f"GET {url} (attempt {attempt+1})")
            r = requests.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
            if r.status_code == 200:
                r.encoding = r.apparent_encoding or 'utf-8'
                return r.text
            else:
                logging.error(f"HTTP {r.status_code}")
                return None
        except Exception as e:
            logging.warning(f"Request failed: {e}")
            time.sleep(1.0)
    return None

def find_twist_anchor(soup):
    # A: by id/anchor
    anchor = soup.find(id="Twist-Deck")
    if anchor:
        logging.info("Found section by id 'Twist-Deck'")
        return anchor
    # B: by searching for a heading that contains 'Twist'
    heading = soup.find(lambda tag: tag.name in ["h1","h2","h3","h4"] and "Twist" in tag.get_text())
    if heading:
        logging.info("Found heading containing 'Twist'")
        return heading
    logging.error("Could not find 'Twist' anchor/heading on page")
    return None

def is_section_heading(tag, base_level=None):
    if not isinstance(tag, Tag): 
        return False
    if tag.name not in ["h1","h2","h3","h4","h5","h6"]:
        return False
    if base_level:
        return tag.name == base_level
    return True

def extract_cards_from_anchor(anchor):
    cards = []
    # Determine base heading level if anchor is a heading
    base_level = anchor.name if anchor.name in ["h1","h2","h3","h4","h5","h6"] else None

    # iterate siblings after anchor
    for sib in anchor.find_next_siblings():
        # if next major section reached, stop (another heading at same level or higher)
        if is_section_heading(sib, base_level):
            logging.info("Reached next section heading; stopping collection of Twist cards.")
            break

        # Heuristic: a card may be in a <div> or <p>; check if contains a bold/title then text
        # Try to detect card title
        title = None
        text_parts = []

        # Case: sib contains multiple <p> where first is title bolded
        # Search inside sibling for bold/strong or header tag
        bold = sib.find(lambda t: t.name in ["strong","b","h4","h5","h6"])
        if bold and bold.get_text(strip=True):
            ctitle = bold.get_text(strip=True)
            # remove bold text from copy to avoid duplication
            # collect remaining visible text nodes in sib
            for child in sib.descendants:
                if isinstance(child, NavigableString):
                    s = child.strip()
                    if s and s != ctitle:
                        text_parts.append(s)
            title = ctitle

        # If no bold, maybe the sibling itself is a p with strong in it, or the first line is title
        if not title:
            # try first <p> content: if it seems like a title (short, capitalized), take it
            first_p = sib.find("p")
            if first_p:
                ptext = first_p.get_text(strip=True)
                # heuristique: si moins de 6 mots et contient majuscule initiale -> titre possible
                if len(ptext.split()) <= 8 and re.match(r"^[A-Z0-9\[\]\"'()\-]+", ptext):
                    title = ptext
                    # rest paragraphs form text
                    for p in sib.find_all("p")[1:]:
                        text_parts.append(p.get_text(" ", strip=True))
            else:
                # fallback: try to see if this sib is a card block with an <h4> child
                hf = sib.find(lambda t: t.name and t.name.startswith("h"))
                if hf:
                    title = hf.get_text(strip=True)
                    # gather following paragraphs inside this sib
                    for p in sib.find_all("p"):
                        text_parts.append(p.get_text(" ", strip=True))

        # If still no title but sib has text and looks like "Name\nDescription", split first line
        if not title:
            raw = sib.get_text("\n", strip=True)
            if raw:
                lines = [ln.strip() for ln in raw.splitlines() if ln.strip()]
                if len(lines) >= 2 and len(lines[0].split()) <= 6:
                    title = lines[0]
                    text_parts.extend(lines[1:])
                elif len(lines) >= 1:
                    # Might be a pure paragraph; skip or keep as description of previous
                    # We'll skip isolated paragraphs without title
                    continue

        if title:
            # join and clean
            combined = "\n".join(text_parts).strip()
            combined = html.unescape(combined)
            combined = re.sub(r"\s+\n", "\n", combined)
            combined = re.sub(r"\n{2,}", "\n\n", combined)
            cards.append({"name": title, "text": combined})
            logging.info(f"Extracted card: {title}")
    return cards

def main():
    html_text = get_page(URL)
    if not html_text:
        logging.error("Failed to retrieve page.")
        return

    soup = BeautifulSoup(html_text, "html.parser")
    anchor = find_twist_anchor(soup)
    if not anchor:
        logging.error("No anchor found; abort.")
        return

    cards = extract_cards_from_anchor(anchor)

    # Fallback: if extraction failed, try alternate heuristic: find all headers inside a local container
    if len(cards) < 1:
        logging.info("Fallback extraction: find headings under same parent.")
        parent = anchor.parent
        for hdr in parent.find_all(lambda t: t.name in ["h3","h4","h5"]):
            # consider hdr text as title and collect following siblings until next hdr
            title = hdr.get_text(strip=True)
            content_parts = []
            for s in hdr.find_next_siblings():
                if s.name and s.name.startswith("h"):
                    break
                content_parts.append(s.get_text(" ", strip=True))
            cards.append({"name": title, "text": "\n".join(part for part in content_parts if part)})
    logging.info(f"Total cards extracted: {len(cards)}")

    # Final cleanup: normalize title names (strip trailing colons)
    for c in cards:
        c["name"] = c["name"].strip().rstrip(":")

    out = {"twist_cards": cards}
    with open("twist_cards.json", "w", encoding="utf-8") as f:
        json.dump(out, f, ensure_ascii=False, indent=2)
    logging.info("Wrote twist_cards.json")

if __name__ == "__main__":
    main()
