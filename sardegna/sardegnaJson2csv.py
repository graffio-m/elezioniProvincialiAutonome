import json
import csv
import requests

# URL del file JSON online
json_url = 'https://amministrazioneaperta.regione.sardegna.it/feedprovasier2019.php'

# Passo 1: Scarica il file JSON online e convertilo in un array Python
response = requests.get(json_url)
json_data = response.json()

# Estrai l'array 'risultati' dall'elemento
risultati_array = json_data.get('stats', {}).get('regionale', {}).get('risultati', {}).get('voti_presidente',[])

    # Rimuovi le chiavi specificate 
del risultati_array['sezioni_scrutinate']
del risultati_array['voti_tot']
del risultati_array['consolidato']
del risultati_array['aggiornamento']

# print(risultati_array)
# Assicurati che l'array 'risultati' contenga almeno un elemento prima di procedere

if risultati_array:

    # Ordina l'array di dizionari in base alla chiave 'voti'
    risultati_ordinati = dict(sorted(risultati_array.items(), key=lambda item: item[1]['voti'], reverse=True))

    # Scrivi l'array ordinato in un file CSV
#    print (risultati_ordinati)

# Specifica il nome del file CSV
csv_file = 'output_py.csv'
with open(csv_file, 'w', newline='') as file:
    # Crea un oggetto writer CSV
    writer = csv.writer(file)

    # Scrivi l'intestazione
    writer.writerow(['Id', 'Denominazione', 'Denominazione Coalizione', 'Voti', 'Percentuale', 'Voti Coalizione', 'Percentuale Coalizione'])

    # Scrivi i dati nel file CSV
    for key, values in risultati_ordinati.items():
        writer.writerow([key, values['denominazione'], values['denominazione_coalizione'], values['voti'], values['percent'], values['voti_coalizione'], values['percent_coalizione']])

print(f'Il file CSV "{csv_file}" è stato creato con successo.')


""" 
if risultati_array:
    # Estrai l'ultimo elemento dall'array 'risultati'
    ultimo_elemento = risultati_array[-1]

    # Scrivi l'ultimo elemento in un file CSV
    csv_file = 'output.csv'
    with open(csv_file, 'w', newline='') as csv_file:
        # Crea un oggetto writer CSV
        csv_writer = csv.writer(csv_file)

        # Scrivi l'intestazione del CSV (se necessario)
        csv_writer.writerow(ultimo_elemento.keys())

        # Scrivi l'ultimo elemento nel CSV
        csv_writer.writerow(ultimo_elemento.values())

    print(f'Il file CSV "{csv_file}" è stato creato con successo.')
else:
    print('L\'array "risultati" è vuoto. Nessun dato da esportare in CSV.')


 """


