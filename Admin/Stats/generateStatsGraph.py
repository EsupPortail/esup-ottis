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
import plotly.figure_factory as ff

mydb = mysql.connector.connect(
user = "root",
password = "root",
host = "localhost",
database = "connexion_data")
mycursor = mydb.cursor()

months_in_order = ['January','February','March','April','May','June','July','August','September','October','November','December']


#################################### Number uses per month ####################################

nb_uses = []
mycursor.execute("SELECT date_year, date_month, COUNT(id) FROM db_statslog GROUP BY date_month, date_year;")
for x in mycursor:
  nb_uses.append(x)
nb_uses_df = pd.DataFrame(data=nb_uses, columns=("Year","Month","Number of uses"))

fig = go.Figure()
for year, group in nb_uses_df.groupby("Year"):
    fig.add_trace(go.Scatter(x=group["Month"], y=group["Number of uses"], name=year,
      hovertemplate="Year = %s<br>Month = %%{x}<br>Number of users = %%{y}<extra></extra>"% year, offsetgroup="all-uses",
        legendgroup="all-uses",legendgrouptitle_text="All uses"))

fig.update_layout(
  title="Number of uses per month",
  xaxis_title="Month",
  yaxis_title="Number of uses",
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
fig.write_html("uses_per_months.html")


#################################### Nb users per month ####################################

nb_users = []
mycursor.execute("SELECT COUNT(DISTINCT user_id), date_month, date_year FROM db_statslog GROUP BY date_month, date_year;")
for x in mycursor:
  nb_users.append(x)
nb_users_df = pd.DataFrame(data=nb_users, columns=("Number of users", "Month", "Year"))

nb_users_df.Month = pd.Categorical(nb_users_df.Month,categories=months_in_order,ordered=True)
nb_users_df = nb_users_df.sort_values('Month')
nb_users_df = nb_users_df.sort_values('Year')

first_use = []
mycursor.execute("SELECT a.user_id, a.date_month, a.date_year FROM db_statslog AS a JOIN (SELECT user_id, min(id) AS id FROM db_statslog GROUP BY user_id) AS b ON a.user_id = b.user_id WHERE a.id = b.id;")
for x in mycursor:
  first_use.append(x)
first_use_df = pd.DataFrame(data=first_use, columns=("user_id", "Month", "Year"))

first_use_df = first_use_df.groupby(['Month','Year'])['user_id'].agg('count').reset_index()
first_use_df = first_use_df.rename(columns={"user_id": "New users"})
first_use_df.Month = pd.Categorical(first_use_df.Month,categories=months_in_order,ordered=True)
first_use_df = first_use_df.sort_values('Month')

fig = go.Figure()
for year, group in nb_users_df.groupby("Year"):
    fig.add_trace(go.Scatter(x=group["Month"], y=group["Number of users"], name=year,
      hovertemplate="Year = %s<br>Month = %%{x}<br>Number of users = %%{y}<extra></extra>"% year, offsetgroup="all-users",
        legendgroup="all-users",legendgrouptitle_text="All users"))

for year, group in first_use_df.groupby("Year"):
    fig.add_trace(go.Bar(x=group["Month"], y=group["New users"], name=year,
      hovertemplate="Year = %s<br>Month = %%{x}<br>New users = %%{y}<extra></extra>"% year, offsetgroup="new-users",
        legendgroup="new-users", legendgrouptitle_text="New users"))

fig.update_layout(
  title="Number of (new) users per month",
  xaxis_title="Month",
  yaxis_title="Number of users",
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
fig.write_html("users_per_months.html")


#################################### Nb uses and users since the beginning group by origin ####################################

mycursor.execute("SELECT date_month, date_year  FROM db_statslog limit 1;")
for x in mycursor:
  first_date =  x

users_by_origin = []
mycursor.execute("SELECT origin, COUNT(DISTINCT user_id) FROM db_statslog GROUP BY origin;")
for x in mycursor:
  users_by_origin.append(x)
users_by_origin_df = pd.DataFrame(data=users_by_origin, columns=("Origin", "Number of users"))

uses_by_origin = []
mycursor.execute("SELECT origin, COUNT(id) FROM db_statslog GROUP BY origin;")
for x in mycursor:
  uses_by_origin.append(x)
uses_by_origin_df = pd.DataFrame(data=uses_by_origin, columns=("Origin", "Number of uses"))
by_origin_df = pd.merge(users_by_origin_df, uses_by_origin_df, how='right', on='Origin')

tot_distinct = ["Total distinct"]
mycursor.execute("SELECT COUNT(DISTINCT user_id) FROM db_statslog;")
for x in mycursor:
  tot_distinct.append(x[0])
mycursor.execute("SELECT COUNT(id) FROM db_statslog;")
for x in mycursor:
  tot_distinct.append(x[0])
by_origin_df.loc[len(by_origin_df.index)] = tot_distinct

fig = go.Figure(data=[go.Table(
  header=dict(values=list(by_origin_df.columns),
              line_color='black',
              fill_color='rgb(52,82,255)',
              align='center',
              font=dict(color='white'),
              height=30),
  cells=dict(values=[by_origin_df['Origin'], by_origin_df['Number of users'], by_origin_df['Number of uses']],
              line_color='black',
              fill=dict(color=['white']),
              font=dict(color=['black']),
              align='center',
              height=30))
])

fig.update_layout(
  title="Number of users and uses since "+first_date[0] +" "+str(first_date[1]),
  width=700, 
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
fig.write_html("uses_users_origin.html")


#################################### Tools used ####################################

tools_used = []
mycursor.execute("SELECT origin, tool, COUNT(id) FROM db_statslog GROUP BY origin, tool ;")
for x in mycursor:
  tools_used.append(x)
tools_used_df = pd.DataFrame(data=tools_used, columns=("Origin","Tool","Number of uses"))
# tools_used_df = tools_used_df.sort_values(by=['Tool'])

fig = go.Figure(data=[go.Table(
  header=dict(values=['Origin', 'Tool','Number of uses'],
              line_color='black',
              fill_color='rgb(52,82,255)',
              align='center',
              font=dict(color='white'),
              height=30),
  cells=dict(values=[tools_used_df['Origin'],tools_used_df['Tool'],tools_used_df['Number of uses'],],
              line_color='black',
              fill=dict(color=['white']),
              font=dict(color=['black']),
              align='center',
              height=30))
])

fig.update_layout(
  title="Tools used since "+first_date[0] +" "+str(first_date[1]),
  width=700, 
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
fig.write_html("tools_used.html")

#################################### Deepl usage ####################################

deepl_per_month = []
mycursor.execute("SELECT date_year, date_month, MAX(percentage_char), MAX(characters) FROM db_translatelog GROUP BY date_year, date_month;")
for x in mycursor:
  deepl_per_month.append(x)
print(deepl_per_month)
deepl_per_month_df = pd.DataFrame(data=deepl_per_month, columns=("Year", "Month","Percentage", "Characters"))
deepl_per_month_df["Percentage"] = deepl_per_month_df["Percentage"]/100

fig = go.Figure()

for year, group in deepl_per_month_df.groupby("Year"):
    fig.add_trace(go.Scatter(x=group["Month"], y=group["Characters"], name=year, yaxis='y',
      hovertemplate="Year = %s<br>Month = %%{x}<br>Number of characters used = %%{y}<extra></extra>"% year))


for year, group in deepl_per_month_df.groupby("Year"):
    fig.add_trace(go.Scatter(x=group["Month"], y=group["Percentage"], name=year, yaxis='y2',
      hovertemplate="Year = %s<br>Month = %%{x}<br>Percentage of characters used = %%{y}<extra></extra>"% year))
 
# Create axis objects
fig.update_layout(           
    yaxis=dict(
        title="Number of characters translated",
        range = [0,1000000]),
                   
    #create 2nd y axis       
    yaxis2=dict(
        title="Percentage",
        overlaying="y",
        side="right",
        tickformat=".0%",
        range = [0,1]))
 
# title
fig.update_layout(
    title_text="Deepl usage",
    font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("deepl_usage_per_month.html")

deepl_per_day = []
mycursor.execute("SELECT DATE(date_complete), MAX(percentage_char), MAX(characters) FROM db_translatelog GROUP BY DATE(date_complete);")
for x in mycursor:
  deepl_per_day.append(x)
deepl_per_day_df = pd.DataFrame(data=deepl_per_day, columns=("Date","Percentage", "Characters"))
deepl_per_day_df["Percentage"] = deepl_per_day_df["Percentage"]/100

fig = go.Figure()

fig.add_trace(go.Scatter(x=deepl_per_day_df["Date"], y=deepl_per_day_df["Percentage"], 
                      name="Percentage", yaxis="y", showlegend=False, 
                      hovertemplate="Date = %{x}<br>Percentage used = %{y}<extra></extra>"))

fig.add_trace(go.Scatter(x=deepl_per_day_df["Date"], y=deepl_per_day_df["Characters"], 
                     name="Number characters", yaxis='y2', showlegend=False, 
                     hovertemplate="Date = %{x}<br>Number of characters used = %{y}<extra></extra>"))

fig.update_layout(     
    yaxis=dict(
        title="Percentage",
        overlaying="y",
        side="right",
        tickformat=".0%",
        range = [0,1]),
    yaxis2=dict(
        title="Number of characters translated",
        range = [0,1000000]))
 
fig.update_layout(
    title_text="Deepl usage per day",
    font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("deepl_usage_per_day.html")