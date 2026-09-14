#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Générer les graphiques correspondants aux différentes statistiques.

    Ce scrip est à lancer à chaque mise à jour de la basse de données.

Usage : 
=======
    py generateStatsGraph.py 
"""

import pandas as pd
import mysql.connector
import plotly.graph_objects as go
import plotly.express as px
import os
from  geopy.geocoders import Nominatim
from ip2geotools.databases.noncommercial import DbIpCity
import time
import requests

mydb = mysql.connector.connect(
user = "root",
password = "root",
host = "localhost",
database = "db_stats")
mycursor = mydb.cursor()

if not os.path.exists("./indicators"):
    os.mkdir("./indicators")
if not os.path.exists("./translation"):
    os.makedirs("./translation")
if not os.path.exists("./users"):
    os.makedirs("./users")
if not os.path.exists("./uses"):
    os.makedirs("./uses")

months_in_order = ['January','February','March','April','May','June','July','August','September','October','November','December']
# mycursor.execute("UPDATE translatelog SET percentage_char = 100*characters/char_max;")
# mydb.commit()

reference_date = '2024-01-01'

def month_number(value):
    if value == 'January':
        return 1
    elif value == 'February':
        return 2
    elif value == 'March':
        return 3
    elif value == 'April':
        return 4
    elif value == 'May':
        return 5
    elif value == 'June':
        return 6
    elif value == 'July':
        return 7
    elif value == 'August':
        return 8
    elif value == 'September':
        return 9
    elif value == 'October':
        return 10
    elif value == 'November':
        return 11
    elif value == 'December':
        return 12

# mycursor = mydb.cursor()
# mycursor.execute("ALTER TABLE statslog DROP external;")
# mydb.commit()

# mycursor = mydb.cursor()
# mycursor.execute("ALTER TABLE statslog ADD is_external BOOLEAN DEFAULT 0;")
# mydb.commit()

# mycursor = mydb.cursor()
# mycursor.execute("ALTER TABLE statslog ADD random_path TEXT;")
# mydb.commit()

# mycursor = mydb.cursor()
# mycursor.execute("ALTER TABLE statslog DROP random_path;")
# mydb.commit()

# mycursor = mydb.cursor()
# mycursor.execute("ALTER TABLE statslog ADD char_translated INT DEFAULT 0;")
# mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='master' WHERE (origin='VM master' or origin='/var/www/html/AutomaticTranslator-master');")
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='master/OMIST_Tools' WHERE origin='/var/www/html/AutomaticTranslator-master/OMIST_Tools';")
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='test' WHERE (origin='VM test' or origin='/var/www/html/AutomaticTranslator-test');")
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='test/OMIST_Tools' WHERE origin='/var/www/html/AutomaticTranslator-test/OMIST_Tools';")
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='external' WHERE origin='/var/www/html/AutomaticTranslator-external';")
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog SET origin='external/OMIST_Tools' WHERE origin='/var/www/html/AutomaticTranslator-external/OMIST_Tools';")
mydb.commit()

#################################### Number uses per month ####################################

nb_uses = []
mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(id) FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s) GROUP BY date_month, date_year, origin, is_external", (reference_date,))
# mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(id) FROM statslog GROUP BY date_month, date_year, origin, is_external;")
# mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(id) FROM statslog WHERE date_complete >=%s GROUP BY date_month, date_year, origin, is_external", (reference_date,))

for x in mycursor:
  nb_uses.append(x)
nb_uses_raw = pd.DataFrame(data=nb_uses, columns=("Month", "Year", "Origin", "External", "Number of uses"))
nb_uses_raw.loc[nb_uses_raw["External"].isna(), "External"] = 0.0
nb_uses_raw["External"] = nb_uses_raw["External"].astype(str)
nb_uses_raw.loc[nb_uses_raw["External"] == "1.0", "External"] = "External to NU"
nb_uses_raw.loc[nb_uses_raw["External"] == "0.0", "External"] = "Internal to NU"

nb_uses_df = nb_uses_raw
nb_uses_df['Month_number']= nb_uses_df['Month'].map(month_number)
nb_uses_df['Date'] = pd.to_datetime(dict(year=nb_uses_df['Year'], month=nb_uses_df['Month_number'], day=1))
nb_uses_df = nb_uses_df.drop('Month_number', axis=1)
nb_uses_df = nb_uses_df.sort_values('Date')
# nb_uses_df.Month = pd.Categorical(nb_uses_df.Month,categories=months_in_order,ordered=True)
# nb_uses_df = nb_uses_df.sort_values('Month')
# nb_uses_df = nb_uses_df.sort_values('Year')
# nb_uses_df.sort_values(['Month','Year'])

nb_uses_graph = nb_uses_raw
nb_uses_graph['Origin global'] = nb_uses_graph['Origin']
for ind in nb_uses_graph.index:
  if "/OMIST_Tools" in nb_uses_graph['Origin'][ind] : 
    nb_uses_graph.loc[ind, 'Origin global'] = nb_uses_graph['Origin'][ind][:-12]
nb_uses_graph = nb_uses_graph.drop(columns = ['Origin'])
nb_uses_graph['Month_number']= nb_uses_graph['Month'].map(month_number)
nb_uses_graph['Date'] = pd.to_datetime(dict(year=nb_uses_graph['Year'], month=nb_uses_graph['Month_number'], day=1))
nb_uses_graph = nb_uses_graph.groupby(['Date', 'Origin global', 'External'])['Number of uses'].sum().reset_index()

fig = go.Figure()
# for origin, group in nb_uses_graph.groupby("Origin global"):
#     fig.add_trace(go.Bar(
#       x=group["Date"], 
#       y=group["Number of uses"], 
#       name=origin,
#       customdata=group[["External"]],
#       hovertemplate="%%{customdata}<br>Origin = %s<br>Date = %%{x}<br>Number of uses = %%{y}<extra></extra>"% origin
#       )
#     )

for origin, group in nb_uses_graph.groupby("External"):
    fig.add_trace(go.Bar(
      x=group["Date"], 
      y=group["Number of uses"], 
      name=origin,
      hovertemplate="Origin = %s<br>Date = %%{x}<br>Number of uses = %%{y}<extra></extra>"% origin
      )
    )

fig.update_layout(
  title={
      'text':"Number of uses per month", 
      'font':{
      'size':30, 
      'color':'black'}
    },
  xaxis_title="Month",
  yaxis_title="Number of uses",
  barmode='stack',
  height=450,
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("uses/uses_per_months_graph.html")
nb_uses_df.to_csv('uses/uses_per_months_tab.csv')  


#################################### Nb users per month ####################################

nb_users = []
mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(DISTINCT user_id) FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s) GROUP BY date_month, date_year, origin, is_external", (reference_date,))
# mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(DISTINCT user_id) FROM statslog GROUP BY date_month, date_year, origin, is_external;")
# mycursor.execute("SELECT date_month, date_year, origin, is_external, COUNT(DISTINCT user_id) FROM statslog WHERE date_complete >=%s GROUP BY date_month, date_year, origin, is_external", (reference_date,))


for x in mycursor:
  nb_users.append(x)
nb_users_raw = pd.DataFrame(data=nb_users, columns=("Month", "Year", "Origin", "External", "Number of users"))
nb_users_raw.loc[nb_users_raw["External"].isna(), "External"] = 0.0
nb_users_raw["External"] = nb_users_raw["External"].astype(str)
nb_users_raw.loc[nb_users_raw["External"] == "1.0", "External"] = "External to NU"
nb_users_raw.loc[nb_users_raw["External"] == "0.0", "External"] = "Internal to NU"


nb_users_df = nb_users_raw
nb_users_df['Month_number']= nb_users_df['Month'].map(month_number)
nb_users_df['Date'] = pd.to_datetime(dict(year=nb_users_df['Year'], month=nb_users_df['Month_number'], day=1))
nb_users_df = nb_users_df.drop('Month_number', axis=1)
nb_users_df = nb_users_df.sort_values('Date')
# nb_users_df.Month = pd.Categorical(nb_users_df.Month,categories=months_in_order,ordered=True)
# nb_users_df = nb_users_df.sort_values('Month')
# nb_users_df = nb_users_df.sort_values('Year')

nb_users_graph = nb_users_raw
nb_users_graph['Origin global'] = nb_users_graph['Origin']
for ind in nb_users_graph.index:
  if "/OMIST_Tools" in nb_users_graph['Origin'][ind] : 
    nb_users_graph.loc[ind, 'Origin global'] = nb_users_graph['Origin'][ind][:-12]
nb_users_graph = nb_users_graph.drop(columns = ['Origin'])
nb_users_graph['Month_number']= nb_users_graph['Month'].map(month_number)
nb_users_graph['Date'] = pd.to_datetime(dict(year=nb_users_graph['Year'], month=nb_users_graph['Month_number'], day=1))
nb_users_graph = nb_users_graph.groupby(['Date', 'Origin global', 'External'])['Number of users'].sum().reset_index()

fig = go.Figure()
# for origin, group in nb_users_graph.groupby("Origin global"):
#     fig.add_trace(go.Bar(
#        x=group["Date"], 
#        y=group["Number of users"], 
#        name=origin,
#        customdata=group[["External"]],
#        hovertemplate="%%{customdata}<br>Origin = %s<br>Date = %%{x}<br>Number of users = %%{y}<extra></extra>"% origin
#       )
#     )

for origin, group in nb_users_graph.groupby("External"):
    fig.add_trace(go.Bar(
       x=group["Date"], 
       y=group["Number of users"], 
       name=origin,
       hovertemplate="Origin = %s<br>Date = %%{x}<br>Number of users = %%{y}<extra></extra>"% origin
      )
    )

fig.update_layout(
  title={
      'text':"Number of users per month", 
      'font':{
      'size':30, 
      'color':'black'}
    },
  xaxis_title="Month",
  yaxis_title="Number of users",
  barmode='stack',
  height=450,
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("users/users_per_months_graph.html")
nb_users_df.to_csv('users/users_per_months_tab.csv')  


#################################### Nb new users per month ####################################

first_use = []
mycursor.execute("SELECT a.user_id, a.date_month, a.date_year, a.origin, a.is_external FROM statslog AS a JOIN (SELECT user_id, min(id) AS id FROM statslog GROUP BY user_id, origin) AS b ON a.user_id = b.user_id WHERE (a.id = b.id AND origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s)", (reference_date,))
# mycursor.execute("SELECT a.user_id, a.date_month, a.date_year, a.origin, a.is_external FROM statslog AS a JOIN (SELECT user_id, min(id) AS id FROM statslog GROUP BY user_id, origin) AS b ON a.user_id = b.user_id WHERE a.id = b.id;")
# mycursor.execute("SELECT a.user_id, a.date_month, a.date_year, a.origin, a.is_external FROM statslog AS a JOIN (SELECT user_id, min(id) AS id FROM statslog GROUP BY user_id, origin) AS b ON a.user_id = b.user_id WHERE (a.id = b.id AND date_complete >=%s)", (reference_date,))

for x in mycursor:
  first_use.append(x)
first_use_raw = pd.DataFrame(data=first_use, columns=("user_id", "Month", "Year", "Origin", "External"))
first_use_raw.loc[first_use_raw["External"].isna(), "External"] = 0.0
first_use_raw["External"] = first_use_raw["External"].astype(str)
first_use_raw.loc[first_use_raw["External"] == "1.0", "External"] = "External to NU"
first_use_raw.loc[first_use_raw["External"] == "0.0", "External"] = "Internal to NU"

first_use_df = first_use_raw.groupby(['Month','Year', 'Origin', 'External'])['user_id'].agg('count').reset_index()
first_use_df = first_use_df.rename(columns={"user_id": "New users"})
first_use_df['Month_number']= first_use_df['Month'].map(month_number)
first_use_df['Date'] = pd.to_datetime(dict(year=first_use_df['Year'], month=first_use_df['Month_number'], day=1))
first_use_df = first_use_df.drop('Month_number', axis=1)
first_use_df = first_use_df.sort_values('Date')
# first_use_df.Month = pd.Categorical(first_use_df.Month,categories=months_in_order,ordered=True)
# first_use_df = first_use_df.sort_values('Month')
# first_use_df = first_use_df.sort_values('Year')

first_use_graph = first_use_raw
first_use_graph['Origin global'] = first_use_graph['Origin']
for ind in first_use_graph.index:
  if "/OMIST_Tools" in first_use_graph['Origin'][ind] : 
    first_use_graph.loc[ind, 'Origin global'] = first_use_graph['Origin'][ind][:-12]
first_use_graph = first_use_graph.drop(columns = ['Origin'])
first_use_graph = first_use_graph.groupby(['Month','Year', 'Origin global', 'External'])['user_id'].agg('count').reset_index()
first_use_graph = first_use_graph.rename(columns={"user_id": "New users origin"})
first_use_graph['Month_number']= first_use_graph['Month'].map(month_number)
first_use_graph['Date'] = pd.to_datetime(dict(year=first_use_graph['Year'], month=first_use_graph['Month_number'], day=1))


fig = go.Figure()
# for origin, group in first_use_graph.groupby("Origin global"):
#     fig.add_trace(go.Bar(
#        x=group["Date"], 
#        y=group["New users origin"], 
#        name=origin,
#        customdata=group[["External"]],
#        hovertemplate="%%{customdata}<br>Origin = %s<br>Date = %%{x}<br>New users = %%{y}<extra></extra>"% origin,
#       )
#     )

for origin, group in first_use_graph.groupby("External"):
    fig.add_trace(go.Bar(
       x=group["Date"], 
       y=group["New users origin"], 
       name=origin,
       hovertemplate="Origin = %s<br>Date = %%{x}<br>New users = %%{y}<extra></extra>"% origin,
      )
    )

fig.update_layout(
  title={
      'text':"Number of new users per month", 
      'font':{
      'size':30, 
      'color':'black'}
    },
  xaxis_title="Month",
  yaxis_title="Number of new users",
  barmode='stack',
  height=450,
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("users/new_users_per_months_graph.html") 
first_use_df.to_csv('users/new_users_per_months_tab.csv')  


#################################### [DEPRECATED ?] Nb uses and users since the beginning group by origin ####################################

# mycursor.execute("SELECT date_month, date_year  FROM statslog limit 1;")
# for x in mycursor:
#   first_date =  x

# users_by_origin = []
# mycursor.execute("SELECT origin, COUNT(DISTINCT user_id) FROM statslog GROUP BY origin;")
# for x in mycursor:
#   users_by_origin.append(x)
# users_by_origin_df = pd.DataFrame(data=users_by_origin, columns=("Origin", "Number of users"))

# uses_by_origin = []
# mycursor.execute("SELECT origin, COUNT(id) FROM statslog GROUP BY origin;")
# for x in mycursor:
#   uses_by_origin.append(x)
# uses_by_origin_df = pd.DataFrame(data=uses_by_origin, columns=("Origin", "Number of uses"))
# by_origin_df = pd.merge(users_by_origin_df, uses_by_origin_df, how='right', on='Origin')

# tot_distinct = ["Total distinct"]
# mycursor.execute("SELECT COUNT(DISTINCT user_id) FROM statslog;")
# for x in mycursor:
#   tot_distinct.append(x[0])
# mycursor.execute("SELECT COUNT(id) FROM statslog;")
# for x in mycursor:
#   tot_distinct.append(x[0])
# by_origin_df.loc[len(by_origin_df.index)] = tot_distinct

# fig = go.Figure(data=[go.Table(
#   header=dict(values=list(by_origin_df.columns),
#               line_color='black',
#               fill_color='rgb(52,82,255)',
#               align='center',
#               font=dict(color='white'),
#               height=30),
#   cells=dict(values=[by_origin_df['Origin'], by_origin_df['Number of users'], by_origin_df['Number of uses']],
#               line_color='black',
#               fill=dict(color=['white']),
#               font=dict(color=['black']),
#               align='center',
#               height=30))
# ])

# fig.update_layout(
#   title="Number of users and uses since "+first_date[0] +" "+str(first_date[1]),
#   width=700, 
#   font=dict(
#       family="Source sans pro",
#       size=18,
#       color="Black"
#   )
# )
# fig.write_html("uses_users_origin.html")



#################################### Tools used ####################################

# mycursor.execute("SELECT date_month, date_year FROM statslog limit 1;")
# mycursor.execute("SELECT date_month, date_year FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s) LIMIT 1;", (reference_date,))
# for x in mycursor:
#   first_date =  x

tools_used = []
# mycursor.execute("SELECT * FROM statslog;")
mycursor.execute("SELECT tool, COUNT(id) FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s) GROUP BY tool", (reference_date,))
# mycursor.execute("SELECT origin, tool, COUNT(id) FROM statslog GROUP BY origin, tool ;")
# mycursor.execute("SELECT origin, tool, COUNT(id) FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s)) GROUP BY origin, tool ", (reference_date,))

for x in mycursor:
  tools_used.append(x)
tools_used_df = pd.DataFrame(data=tools_used, columns=("Tool","Number of uses"))

tools_used_df['Tool used'] = tools_used_df['Tool']
for ind in tools_used_df.index:
  if "/" in tools_used_df['Tool'][ind] : 
    position = tools_used_df['Tool'][ind].find('/')
    tools_used_df.loc[ind, 'Tool used'] = tools_used_df['Tool'][ind][position+1:-4]
tools_used_df = tools_used_df.drop(columns = ['Tool'])
tools_used_df = tools_used_df.sort_values('Number of uses', ascending=False)

fig = go.Figure(data=[go.Table(
  header=dict(values=['Tool','Number of uses'],
              line_color='black',
              fill_color='rgb(52,82,255)',
              align='center',
              font=dict(color='white'),
              height=30),
  cells=dict(values=[tools_used_df['Tool used'],tools_used_df['Number of uses'],],
              line_color='black',
              fill=dict(color=['white']),
              font=dict(color=['black']),
              align='center',
              height=30))
])

fig.update_layout(
  title={
      'text':"Tools used since "+str(reference_date), 
      'font':{
      'size':30, 
      'color':'black'}
    },
  # width=700, 
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
 
fig.write_html("uses/tools_used_tab.html")
tools_used_df.to_csv('uses/tools_used_tab.csv') 


# #################################### Configuration used ####################################

config_used = []
mycursor.execute("SELECT config_teacher, COUNT(id) FROM statslog WHERE (tool='teacher' AND origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s AND ((config_teacher = 'direct link') OR (LENGTH(config_teacher) = 18))) GROUP BY config_teacher", (reference_date,))
# mycursor.execute("SELECT config_teacher, COUNT(id) FROM statslog WHERE (tool='teacher' AND origin IN ('master', 'master/OMIST_Tools')) GROUP BY config_teacher;")
# mycursor.execute("SELECT config_teacher, COUNT(id) FROM statslog WHERE (tool='teacher' AND date_complete >=%s) GROUP BY config_teacher", (reference_date,))

for x in mycursor:
  config_used.append(x)
config_used_df = pd.DataFrame(data=config_used, columns=("Configuration","Number of uses"))
config_used_df=config_used_df.dropna(axis=0, how='any')

config_used_df['Classroom language'] = config_used_df['Configuration']
config_used_df['Subtitle language'] = config_used_df['Configuration']
config_used_df['Translation bar'] = config_used_df['Configuration']
config_used_df['Transcription bar'] = config_used_df['Configuration']
config_used_df['Wooclap'] = config_used_df['Configuration']
config_used_df['Share tab'] = config_used_df['Configuration']
config_used_df['Microsoft translation'] = config_used_df['Configuration']
config_used_df['Microsoft translation both'] = config_used_df['Configuration']

for i in config_used_df.index:
  if (config_used_df['Configuration'][i] != 'direct link'):
    # Issue for pandas 3.0
    #config_used_df['Classroom language'][i] = config_used_df['Configuration'][i][0]
    #config_used_df['Subtitle language'][i] = config_used_df['Configuration'][i][1]
    #config_used_df['Translation bar'][i] = config_used_df['Configuration'][i][2]
    #config_used_df['Transcription bar'][i] = config_used_df['Configuration'][i][4]
    #config_used_df['Wooclap'][i] = config_used_df['Configuration'][i][7]
    #config_used_df['Share tab'][i] = config_used_df['Configuration'][i][8]
    #config_used_df['Microsoft translation'][i] = config_used_df['Configuration'][i][16]
    #config_used_df['Microsoft translation both'][i] = config_used_df['Configuration'][i][17]
    config_used_df.loc[i,'Classroom language'] = config_used_df['Configuration'][i][0]
    config_used_df.loc[i,'Subtitle language'] = config_used_df['Configuration'][i][1]
    config_used_df.loc[i,'Translation bar'] = config_used_df['Configuration'][i][2]
    config_used_df.loc[i,'Transcription bar'] = config_used_df['Configuration'][i][4]
    config_used_df.loc[i,'Wooclap'] = config_used_df['Configuration'][i][7]
    config_used_df.loc[i,'Share tab'] = config_used_df['Configuration'][i][8]
    config_used_df.loc[i,'Microsoft translation'] = config_used_df['Configuration'][i][16]
    config_used_df.loc[i,'Microsoft translation both'] = config_used_df['Configuration'][i][17]

config_used_df.replace({'A':'french', 'B':'english', 'Y':'yes', 'N':'no'}, inplace=True)
config_used_df = config_used_df.sort_values('Number of uses', ascending=False)

fig = go.Figure(data=[go.Table(
  columnwidth=[2, 1, 1, 1, 1, 1],
  header=dict(values=['Configuration', 'Classroom language','Subtitle language',
              'Direct translation','Direct translation & transcription', 'Number of uses'],
              line_color='black',
              fill_color='rgb(52,82,255)',
              align='center',
              font=dict(color='white'),
              height=30),
  cells=dict(values=[config_used_df['Configuration'],config_used_df['Classroom language'],config_used_df['Subtitle language'],
              config_used_df['Microsoft translation'],config_used_df['Microsoft translation both'], config_used_df['Number of uses'],],
              line_color='black',
              fill=dict(color=['white']),
              font=dict(color=['black']),
              align='center',
              height=30))
])

fig.update_layout(
  title={
      'text':"Configuration used for teacher application", 
      'font':{
      'size':30, 
      'color':'black'}
    },
  # width=700, 
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("uses/config_used_tab.html")
config_used_df.to_csv('uses/config_used_tab.csv')


#################################### Deepl usage ####################################

deepl_per_month = []
mycursor.execute("SELECT date_month, date_year, MAX(percentage_char), MAX(characters), origin_translate FROM translatelog WHERE date_day<7 GROUP BY date_year, date_month, origin_translate;")
# mycursor.execute("SELECT date_month, date_year, MAX(percentage_char), MAX(characters) FROM translatelog WHERE (date_day<7 AND date_complete >=%s) GROUP BY date_year, date_month", (reference_date,))

for x in mycursor:
  deepl_per_month.append(x)
deepl_per_month_df = pd.DataFrame(data=deepl_per_month, columns=("Month", "Year", "Percentage", "Characters", "Origin"))
deepl_per_month_df["Percentage"] = deepl_per_month_df["Percentage"]/100

deepl_per_month_df.Month = pd.Categorical(deepl_per_month_df.Month,categories=months_in_order,ordered=True)
deepl_per_month_df = deepl_per_month_df.sort_values('Month')
deepl_per_month_df = deepl_per_month_df.sort_values('Year')

deepl_per_month_df['Month_number']= deepl_per_month_df['Month'].map(month_number)
deepl_per_month_df['Date'] = pd.to_datetime(dict(year=deepl_per_month_df['Year'], month=deepl_per_month_df['Month_number'], day=1))
deepl_per_month_df = deepl_per_month_df.drop('Month_number', axis=1)
deepl_per_month_df = deepl_per_month_df.sort_values('Date')

deepl_per_month_df['Month_number']= deepl_per_month_df['Month'].map(month_number)
deepl_per_month_df['Date'] = pd.to_datetime(dict(year=deepl_per_month_df['Year'], month=deepl_per_month_df['Month_number'], day=1))

fig = go.Figure()

#Number characters
# for year, group in deepl_per_month_df.groupby("Year"):
#     fig.add_trace(go.Scatter(
#       x=group["Month"], 
#       y=group["Characters"], 
#       name=year, 
#       yaxis='y2',
#       hovertemplate="Year = %s<br>Month = %%{x}<br>Number of characters used = %%{y}<extra></extra>"% year
#       )
#     )

#Percentage
for origin, group in deepl_per_month_df.groupby("Origin"):
    fig.add_trace(go.Scatter(
      x=group["Date"], 
      y=group["Percentage"], 
      name=origin, 
      yaxis='y',
      hovertemplate="From 7 of the previous month to 7 %{x} <br>Percentage used = %{y}<extra></extra>"
      )
    )
 
fig.update_layout(             
    yaxis=dict(
        title="Percentage",
        overlaying="y",
        # side="right",
        tickformat=".0%",
        range = [0,1]),    
    # yaxis2=dict(
    #     title="Number of characters translated",
    #     range = [0,1000000]
    #   ),
    xaxis_title="7th of the month",
    title={
      'text':"Translator usage", 
      'font':{
      'size':30, 
      'color':'black'}
    },
    height=450,
    font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("translation/deepl_usage_per_month_graph.html")
deepl_per_month_df.to_csv('translation/deepl_usage_per_month_tab.csv')  


#################################### Deepl usage per day ####################################

deepl_per_day = []
mycursor.execute("SELECT DATE(date_complete), MAX(percentage_char), MAX(characters), origin_translate FROM translatelog GROUP BY DATE(date_complete), origin_translate;")
# mycursor.execute("SELECT DATE(date_complete), MAX(percentage_char), MAX(characters) FROM translatelog WHERE date_complete >=%s GROUP BY DATE(date_complete)", (reference_date,))

for x in mycursor:
  deepl_per_day.append(x)

deepl_per_day_df = pd.DataFrame(data=deepl_per_day, columns=("Date","Percentage", "Characters", "Origin"))
deepl_per_day_df["Percentage"] = deepl_per_day_df["Percentage"]/100

last_row = deepl_per_day_df.iloc[[-1]]
last_origin = last_row['Origin'].to_string(index=False)

fig = go.Figure()

#Percentage
for origin, group in deepl_per_day_df.groupby("Origin"):
  fig.add_trace(go.Scatter(
      x=group["Date"], 
      y=group["Percentage"], 
      name=origin, 
      # yaxis="y", 
      # showlegend=False, 
      # customdata=deepl_per_day_df[["Characters"]],  
      # hovertemplate="Date = %{x}<br>Cumulative percentage = %{y}<br>Number of characters used = %{customdata[0]}<extra></extra>"
      hovertemplate="Date = %{x}<br>Cumulative percentage = %{y}<extra></extra>"
      )
    )

#Number characters
# fig.add_trace(go.Scatter(
#     x=deepl_per_day_df["Date"], 
#     y=deepl_per_day_df["Characters"], 
#     name="Number characters", 
#     yaxis='y2', 
#     showlegend=False, 
#     hovertemplate="Date = %{x}<br>Number of characters used = %{y}<extra></extra>"
#     )
#   )

# Create axis objects
fig.update_layout(     
    yaxis=dict(
        title="Percentage",
        overlaying="y",
        # side="right",
        tickformat=".0%",
        range = [0,1]),
    # yaxis2=dict(
    #     title="Number of characters translated",
    #     range = [0,1000000]
    # ),
    title={
      'text':"Translator usage per day", 
      'font':{
        'size':30, 
        'color':'black'}
    },
    height=450,
    font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
 
fig.write_html("translation/deepl_usage_per_day_graph.html")
deepl_per_day_df.to_csv('translation/deepl_usage_per_day_tab.csv') 


#################################### Indicators month ####################################

fig = go.Figure()

# first_use_df_month = first_use_df.sort_values('Year')
# if first_use_df_month.iloc[-1]["Month"] == nb_uses_df.iloc[-1]["Month"]:
#    new_users_value = first_use_df_month.iloc[-1]["New users"]
#    previous_users_value = first_use_df_month.iloc[-2]["New users"]
# else:
#    new_users_value = 0
#    previous_users_value = first_use_df_month.iloc[-1]["New users"]

first_use_df_month = first_use_graph.groupby(['Date'])['New users origin'].sum()
first_use_df_month = first_use_df_month.sort_index(ascending=True)

if first_use_df_month.index[-1] == nb_uses_graph.iloc[-1]["Date"]:
   new_users_value = first_use_df_month.iloc[-1]
   previous_users_value = first_use_df_month.iloc[-2]
else:
   new_users_value = 0
   previous_users_value = first_use_df_month.iloc[-1]

fig.add_trace(go.Indicator(
    mode = "number",
    value = new_users_value,
    title= {
       'text':'New users',
       'font':{
         'size':40}
      },
    domain = {'x': [0, 0.2], 'y': [0.2, 1]})
    )

fig.add_trace(go.Indicator(
    mode = "delta",
    value = new_users_value,
    title= {
       'text':'Compared to the previous month',
       'font':{
         'size':20, 
         'color':'grey'}
      },
    delta = {
       'reference': previous_users_value , 
       'font_size' : 50,
      },
    domain = {'x': [0, 0.2], 'y': [0, 0.5]}))


nb_uses_month = nb_uses_graph.groupby(['Date'])['Number of uses'].sum()
nb_uses_month = nb_uses_month.sort_index(ascending=True)

fig.add_trace(go.Indicator(
    mode = "number",
    value = nb_uses_month.iloc[-1],
    title= {
       'text':'Uses',
       'font':{
         'size':40}
      },
    delta = {'reference': nb_uses_month.iloc[-2], 'relative': True, 'valueformat':'.0%'},
    domain = {'x': [0.4, 0.6], 'y': [0.2, 1]}))

fig.add_trace(go.Indicator(
    mode = "delta",
    value = nb_uses_month.iloc[-1],
    title= {
       'text':'Compared to the previous month',
       'font':{
         'size':20, 
         'color':'grey'}
      },
    delta = {
       'reference': nb_uses_month.iloc[-2], 
       'relative': True, 
       'valueformat':'.0%',
       'font_size' : 50,
      },
    domain = {'x': [0.4, 0.6], 'y': [0, 0.5]}))


if (len(deepl_per_day_df.index)==0):
   value_percentage = 0
else:
   value_percentage = deepl_per_day_df.iloc[-1]["Percentage"]*100

fig.add_trace(go.Indicator(
    mode = "gauge+number",
    value = value_percentage,
    title= {
       'text':last_origin + ' usage<br><span style="font-size:20;color:gray">Reset on the 7th</span><br>',
       'font':{
         'size':40}
      },
    number = {'suffix': "%"},
    gauge = {'axis': {'range': [None, 100]},
             'bar': {'color': "gray"},
             'steps' : [
                 {'range': [0, 50], 'color': "#cbf078"},
                 {'range': [50, 75], 'color': "#f8f398"},
                 {'range': [75, 90], 'color': "#f1b963"},
                 {'range': [90, 100], 'color': "#e46161"}]},
    domain = {'x': [0.8, 1], 'y': [0, 1]}))

fig.update_layout(
   title= {
      'text':nb_uses_graph.iloc[-1]["Date"].strftime('%B %Y'), 
      'font':{
         'size':45, 
         'color':'black'}
      },
   width = 1600, 
   font = {'family': "Source sans pro"})

fig.write_image("indicators/indicator_month.png")


#################################### Indicators total ####################################

fig = go.Figure()

fig.add_trace(go.Indicator(
    mode = "number",
    value = first_use_df['New users'].sum(),
    title= {
       'text':'Users',
       'font':{
         'size':40}
      },
    number = {
       'prefix': '+',
       'font':{'color':'green'}
       },
    domain = {'x': [0, 0.2], 'y': [0, 1]})
    )

fig.add_trace(go.Indicator(
    mode = "number",
    value = nb_uses_df['Number of uses'].sum(),
    title= {
      'text':'Uses',
       'font':{
         'size':40}
      },
    domain = {'x': [0.4, 0.6], 'y': [0, 1]}))

fig.update_layout(
   title= {
      'text':"Since "+str(reference_date), 
      'font':{
         'size':45, 
         'color':'black'}
      },
   width = 1600, 
   font = {'family': "Source sans pro"})

fig.write_image("indicators/indicator_tot.png")

#################################### Geolocalisation ####################################

ip_to_translate = []
ip_translated_df = pd.DataFrame(columns=['IP_address', 'City', 'Country', 'Latitude', 'Longitude'])
mycursor.execute("SELECT ip_address FROM statslog WHERE (ip_address IS NOT NULL and city IS NULL and latitude IS NULL) GROUP BY ip_address;")
# mycursor.execute("SELECT ip_address FROM statslog WHERE (ip_address IS NOT NULL and city IS NULL and latitude IS NULL) WHERE date_complete >=%s GROUP BY ip_address", (reference_date,))

for x in mycursor:
  ip_to_translate.append(x)
ip_to_translate_df = pd.DataFrame(data=ip_to_translate, columns=["IP_address"])

####ip2geotools
# i = 0
# for ind in ip_to_translate_df.index:
#   ip = ip_to_translate_df['IP_address'][ind]
#   res = DbIpCity.get(ip, api_key="free")
#   ip_translated_df.loc[i,'IP_address'] = res.ip_address
#   ip_translated_df.loc[i,'City'] = res.city
#   ip_translated_df.loc[i,'Country'] = res.country
#   if (res.latitude != None):
#     ip_translated_df.loc[i,'Latitude'] = res.latitude
#   if (res.latitude != None):
#     ip_translated_df.loc[i,'Longitude'] = res.longitude
#   i = i+1
#   time.sleep(5)

####ipapi
i = 0
#fields_correspondance={'City':'city', 'Country': 'country_name', 'Latitude': 'latitude', 'Longitude':'longitude'}
fields_correspondance={'City':'city', 'Country': 'country', 'Latitude': 'lat', 'Longitude':'lon'}

for ind in ip_to_translate_df.index:
  ip = ip_to_translate_df['IP_address'][ind]
  #response = requests.get(f'https://ipapi.co/{ip}/json/').json()
  response = requests.get(f'http://ip-api.com/json/{ip}').json()
  ip_translated_df.loc[i,'IP_address'] = ip
  if (response.get(fields_correspondance["City"]) != None):
    ip_translated_df.loc[i,'City'] = response.get(fields_correspondance["City"])
  if (response.get(fields_correspondance["Country"]) != None):
    ip_translated_df.loc[i,'Country'] = response.get(fields_correspondance["Country"])
  if (response.get(fields_correspondance["Latitude"]) != None):
    ip_translated_df.loc[i,'Latitude'] = response.get(fields_correspondance["Latitude"])
  if (response.get(fields_correspondance["Longitude"]) != None):
    ip_translated_df.loc[i,'Longitude'] = response.get(fields_correspondance["Longitude"])
  i = i+1
  time.sleep(5)

for j in ip_translated_df.index:
  mycursor = mydb.cursor()
  sql = "INSERT INTO iplog (IP_address, city, country, latitude, longitude) VALUES (%s, %s, %s, %s, %s)"
  val = ip_translated_df.loc[j].values.flatten().tolist()
  if not pd.isna(val[1]):
    mycursor.execute(sql, val)
    mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog a JOIN iplog b on a.ip_address = b.IP_address set a.city=b.city, a.country=b.country, a.latitude=b.latitude, a.longitude=b.longitude WHERE (a.city IS NULL and a.latitude IS NULL)")
mydb.commit()

#################################### City with no coordinates ####################################

# geolocator = Nominatim(user_agent="MyApp",proxies={'http':'http://cache.univ-nantes.fr:3128', 'https':'http://cache.univ-nantes.fr:3128'})

# city_to_coordinates = []
# mycursor.execute("SELECT city, country, latitude, longitude FROM statslog WHERE (ip_address IS NOT NULL and latitude IS NULL and longitude IS NULL and city IS NOT NULL and country IS NOT NULL) GROUP BY city, country, latitude, longitude;")
# for x in mycursor:
#   city_to_coordinates.append(x)
# city_to_coordinates_df = pd.DataFrame(data=city_to_coordinates, columns=["City", "Country", "Latitude", "Longitude"])

# for ind in city_to_coordinates_df.index:
#   city = city_to_coordinates_df['City'][ind]
#   country = city_to_coordinates_df['Country'][ind]
#   loc = geolocator.geocode(city+','+ country)
#   city_to_coordinates_df.loc[ind,'Latitude'] = loc.latitude
#   city_to_coordinates_df.loc[ind,'Longitude'] = loc.longitude
#   time.sleep(5)

# mycursor = mydb.cursor()
# for index, row in city_to_coordinates_df.iterrows():
#     sql = 'UPDATE iplog SET latitude = %s, longitude = %s where (city = %s and country= %s)'
#     mycursor.execute(sql, (row['Latitude'], row['Longitude'], row['City'], row['Country'],))
# mydb.commit()

# mycursor = mydb.cursor()
# mycursor.execute("UPDATE statslog a JOIN iplog b on a.ip_address = b.IP_address set a.latitude=b.latitude, a.longitude=b.longitude WHERE (a.latitude IS NULL and a.longitude IS NULL)")
# mydb.commit()

#################################### Map users and uses ####################################

coordinates = []
mycursor.execute("SELECT city, latitude, longitude, COUNT(DISTINCT user_id), COUNT(id) FROM statslog GROUP BY latitude, longitude, city;")
# mycursor.execute("SELECT city, latitude, longitude, COUNT(DISTINCT user_id), COUNT(id) FROM statslog WHERE date_complete >=%s GROUP BY latitude, longitude, city", (reference_date,))

for x in mycursor:
  coordinates.append(x)
coordinates_df = pd.DataFrame(data=coordinates, columns=["City", "Latitude", "Longitude", "Users", "Uses"])
coordinates_map = coordinates_df.dropna(how='any',axis=0)

fig = go.Figure()
fig = px.scatter_geo(coordinates_map,
                    lat="Latitude",
                    lon="Longitude",
                    color="Users",
                    color_continuous_scale = px.colors.sequential.matter, 
                    hover_name="City",
                    title="Users location")

fig.update_traces(marker=dict(size=10))
# fig.update_geos(fitbounds="locations")

fig.write_html("users/map_ipaddress.html")

fig = go.Figure()
fig = px.scatter_geo(coordinates_map,
                    lat="Latitude",
                    lon="Longitude",
                    color="Uses",
                    color_continuous_scale = px.colors.sequential.matter, 
                    hover_name="City",
                    title="Uses location")

fig.update_traces(marker=dict(size=10))
# fig.update_geos(fitbounds="locations")

fig.write_html("uses/map_ipaddress.html")

coordinates_df.to_csv('users/map_ipaddress_tab.csv')


#################################### Database total ####################################

table_tot = []
# mycursor.execute("SELECT * FROM statslog WHERE date_complete;")
mycursor.execute("SELECT * FROM statslog WHERE (origin IN ('master', 'master/OMIST_Tools') AND date_complete >=%s);", (reference_date,))
# mycursor.execute("SELECT * FROM statslog WHERE date_complete >=%s", (reference_date,))

for x in mycursor:
  table_tot.append(x)
table_tot_df = pd.DataFrame(data=table_tot, columns=(mycursor.column_names))
table_tot_df = table_tot_df.sort_values('date_raw')
# table_tot_df = pd.DataFrame(data=table_tot, columns=("id","date_raw","date_complete","date_day","date_month","date_year","user_id","room_id","tool","origin","config_teacher","ip_address", "city", "country", "latitude", "longitude", "external"))
table_tot_df.to_csv('table_tot.csv')  