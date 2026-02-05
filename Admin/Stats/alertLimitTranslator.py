import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import mysql.connector
import pandas as pd

mydb = mysql.connector.connect(
user = "root",
password = "root",
host = "localhost",
database = "db_stats")
mycursor = mydb.cursor()

last_translator = []
mycursor.execute("SELECT date_complete, origin_translate, percentage_char, characters FROM translatelog ORDER BY id DESC LIMIT 1;")
for x in mycursor:
  last_translator.append(x)

last_translator_df = pd.DataFrame(data=last_translator, columns=("Date","Origin","Percentage","Characters"))
origin_translator = last_translator_df["Origin"].to_string(index=False)
percentage_translator = int(last_translator_df["Percentage"].to_string(index=False))

if (((origin_translator=="DEEPLPRO") or (origin_translator=="DEEPLFREE")) and (percentage_translator > 95)):
    msg = MIMEMultipart()
    msg['From'] = 'omist.nantesuniversite@gmail.com'
    msg['To'] = 'omist.nantesuniversite@gmail.com'
    msg['Subject'] = '[OMIST] Dépassement des quotas de traductions' 
    message = 'Attention, le quota de '+ origin_translator + ' est à '+str(percentage_translator)+"% de sa capacité de traduction."
    msg.attach(MIMEText(message))
    mailserver = smtplib.SMTP('smtp.gmail.com', 587)
    mailserver.ehlo()
    mailserver.starttls()
    mailserver.ehlo()
    mailserver.login('omist.nantesuniversite@gmail.com', 'whyy jxdl kslm iydm')
    mailserver.sendmail('omist.nantesuniversite@gmail.com', 'omist.nantesuniversite@gmail.com', msg.as_string())
    mailserver.quit()