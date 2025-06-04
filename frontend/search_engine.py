import pandas as pd
import re
import json
import sys
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np

# Load your dataset
df = pd.read_csv('dana_reviews.csv')

# --- Daftar Stop Words Bahasa Indonesia ---
indonesian_stop_words = [
    'yang', 'untuk', 'pada', 'ke', 'dari', 'dengan', 'di', 'dan', 'ini', 'itu',
    'adalah', 'akan', 'atau', 'bisa', 'saat', 'saya', 'kami', 'kita', 'mereka',
    'nya', 'tidak', 'sudah', 'telah', 'juga', 'masih', 'lagi', 'pun', 'saja',
    'seperti', 'terlalu', 'lebih', 'kurang', 'begitu', 'begitu', 'maka', 'oleh',
    'karena', 'namun', 'tetapi', 'walaupun', 'meskipun', 'agar', 'supaya',
    'setelah', 'sebelum', 'saat', 'ketika', 'jika', 'kalau', 'bila', 'apakah',
    'bagaimana', 'mengapa', 'dimana', 'kapan', 'siapa', 'apa', 'begitu', 'cuma',
    'doang', 'emang', 'ga', 'gak', 'kan', 'kok', 'loh', 'nih', 'sih', 'udah',
    'punya', 'harus', 'banyak', 'sedikit', 'sering', 'jarang', 'selalu', 'pernah',
    'tidak', 'bukan', 'jangan', 'belum', 'sampai', 'sejak', 'segera', 'sekali',
    'selain', 'seluruh', 'sementara', 'semua', 'sendiri', 'seolah', 'sepanjang',
    'seperlunya', 'seperti', 'sepertinya', 'sesudah', 'sesuatu', 'setelah',
    'setiap', 'setidaknya', 'siapapun', 'sini', 'situ', 'suatu', 'tanpa', 'tapi',
    'tiap', 'untuk', 'usah', 'wah', 'yakni', 'yaitu', 'zaman', 'tanpa', 'tapi',
    'tiap', 'untuk', 'usah', 'wah', 'yakni', 'yaitu', 'zaman', 'dana', 'aplikasi',
    'app', 'apk', 'e-wallet', 'dompet', 'digital', 'uang', 'saldo', 'fitur', 'pakai',
    'banget', 'banget', 'bgt', 'bgt', 'sangat', 'sekali', 'udah', 'bikin', 'buat'
]

class ReviewSearchEngine:
    def __init__(self, dataframe):
        self.df = dataframe.copy()
        
        if 'content' not in self.df.columns:
            raise ValueError("DataFrame must have 'content' column for reviews.")

        self.df['cleaned_content'] = self.df['content'].apply(
            lambda x: re.sub(r'\s+', ' ', str(x)).strip() if pd.notnull(x) else ''
        )

        self.df_indexed = self.df[self.df['cleaned_content'].str.len() > 0].reset_index(drop=True)

        if self.df_indexed.empty:
            raise ValueError("No valid reviews after cleaning to index.")

        self.vectorizer = TfidfVectorizer(stop_words=indonesian_stop_words, max_features=5000)
        self.tfidf_matrix = self.vectorizer.fit_transform(self.df_indexed['cleaned_content'])

    def search(self, query, top_n=10):
        if not query:
            return []

        cleaned_query = re.sub(r'\s+', ' ', query).strip()

        try:
            query_vec = self.vectorizer.transform([cleaned_query])
        except ValueError:
            return []

        cosine_similarities = cosine_similarity(query_vec, self.tfidf_matrix).flatten()
        related_reviews_indices = cosine_similarities.argsort()[:-top_n-1:-1]

        results = []
        for idx in related_reviews_indices:
            if cosine_similarities[idx] > 0:
                review = self.df_indexed.iloc[idx].to_dict()
                review['similarity_score'] = float(cosine_similarities[idx])
                results.append(review)
                
                if len(results) >= top_n:
                    break

        return results

if __name__ == "__main__":
    # Initialize search engine
    search_engine = ReviewSearchEngine(df)
    
    # Get query from command line argument
    if len(sys.argv) > 1:
        query = sys.argv[1]
        results = search_engine.search(query)
        
        # Convert results to JSON and print
        print(json.dumps(results, ensure_ascii=False, indent=2))
    else:
        print(json.dumps([], ensure_ascii=False))