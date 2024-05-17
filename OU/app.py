import numpy
from flask import Flask, render_template, request, session, send_file, jsonify
import pandas as pd
import io
from bs4 import BeautifulSoup
import requests
import urllib3
from threading import Thread
import re

def star(list123, url, df2, nlis):
    for i in list123:
        hall_ticket_no = i
        payload = {'mbstatus': 'SEARCH', 'htno': hall_ticket_no}
        res = requests.post(url, verify=False, data=payload, allow_redirects=True)
        soup = BeautifulSoup(res.text, 'html.parser')
        data = []
        for row in soup.find_all("tr"):
            row_data = []
            for cell in row.find_all(["th", "td"]):
                row_data.append(cell.text.replace("\n", ' ').strip())
            data.append(row_data)
        if len(data) != 6:
            mins = len(data) - 30
            info = [data[5:7], data[-19 - mins:-10], data[-14:-6]]
            for j in info[1]:
                if len(j) == 5:
                    j.pop(2)
                    z = int(j[0][0])
                    d1 = [int(hall_ticket_no)]
                    for k in j:
                        d1.append(k)

                    for q in info[-1]:
                        if q[0] == str(z):
                            if 'PROMOTED' in q[-2] and not re.search(r'[0-9]', q[-2]):
                                d1.append("PROMOTED")
                            elif 'MALPRACTICE' in q[-2]:
                                d1.append("MALPRACTICE")
                            elif "FAILED" in q[-2]:
                                d1.append("FAILED")
                            else:
                                sgp = q[-2]
                                sgp = sgp[sgp.index('-') + 1:]
                                d1.append(sgp)
                    df2.append(d1)

        else:
            nlis.append(i)


def cheurl(url):
    payload = {'mbstatus': 'SEARCH', 'htno': 245522748065}
    res = requests.post(url, verify=False, data=payload)
    return res.status_code


def start(list_roll1, url1, df2, nlis):
    threads = []
    z = 5
    while len(list_roll1) >= z:
        t1 = Thread(target=star, args=(
            [list_roll1[i] for i in range(z)], url1, df2, nlis))
        threads.append(t1)
        t1.start()
        for i in range(z):
            list_roll1.pop(0)
    t1 = Thread(target=star, args=(list_roll1, url1, df2, nlis))
    threads.append(t1)
    t1.start()
    for t1 in threads:
        t1.join()


app = Flask(__name__)
app.secret_key = 'IHDNAGAB@b'
urllib3.disable_warnings()


@app.route('/', methods=['GET'])  # get ,post
def upload_file():
    session.clear()
    session['check'] = 0
    return render_template('index.html', e_flag=0)

@app.route('/getres', methods=['POST'])
def getres():
    session['lis'] = []
    session['nlis'] = []
    session['url'] = request.form['URL']
    session['s_no'] = request.form['num1']
    session['e_no'] = request.form['num2']
    file = request.files['file']
    if not (session['url'].endswith(".jsp") and "https://www.osmania.ac.in/res07/" in session['url']):
        return render_template('index.html', e_flag=1)

    if len(session['s_no']) == 0 and len(session['e_no']) == 0 and file.filename == '':
        return render_template('index.html', e_flag=2)
    elif (len(session['s_no']) != 12 or len(session['e_no']) != 12) and file.filename == '':
        return render_template('index.html', e_flag=3)
    try:
        if len(session['s_no']) == 12 or len(session['e_no']) == 12:
            session['s_no'] = int(session['s_no'])
            session['e_no'] = int(session['e_no'])
            session['lis'].extend([int(i) for i in range(session['s_no'], session['e_no'] + 1)])
    except ValueError:
        return render_template('index.html', e_flag=3)

    if file:
        session['check']=0
        buff = io.BytesIO(file.stream.read())
        buff.seek(0)
        if file.filename.endswith('.csv'):
            csvd = buff.getvalue().decode('utf-8')
            session['df'] = pd.read_csv(io.StringIO(csvd))
        else:
            session['df'] = pd.read_excel(buff, engine='openpyxl')
        for i in session['df'].values:
            if len(str(i[0])) == 12:
                try:
                    session['lis'].append(int(i[0]))
                except ValueError:
                    session['check'] = 1
            else:
                session['check'] = 2
        session.pop('df')
        if session['check'] == 1:
            return render_template('index.html', e_flag=4)
        if session['check'] == 2:
            return render_template('index.html', e_flag=5)
    session['df2'] = []
    if cheurl(session['url']) != 200:
        return render_template('index.html', e_flag=6)
    start(session['lis'], session['url'], session['df2'], session['nlis'])
    [session['df2'].append([i, 'not found', 'not found', 'not found', 'not found', 'not found']) for i in session['nlis']]
    session['df2'] = sorted(session['df2'], key=lambda l: l[0])
    buffer = io.BytesIO()
    df9 = pd.DataFrame(session['df2'],
                       columns=['ROLL NUMBER', 'Sub code', 'Subject Name', 'Grade Points', 'Grade Secured', 'SGPA'])
    df9.to_excel(buffer, index=False)
    buffer.seek(0)
    session.pop('df2')
    session['file_ready'] = True
    return send_file(buffer, as_attachment=True, download_name='results.xlsx')

@app.route('/check_session_flag', methods=['GET'])
def check_session_flag():
    file_ready = session.get('file_ready', False)
    return jsonify({"fileReady": file_ready})
@app.route('/reset_session_flag', methods=['POST'])
def reset_session_flag():
    session['file_ready'] = False
    return jsonify({"status": "success"})

if __name__ == '__main__':
    app.run()
