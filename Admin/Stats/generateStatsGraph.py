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

table_tot = []
mycursor.execute("SELECT * FROM statslog;")
for x in mycursor:
  table_tot.append(x)
table_tot_df = pd.DataFrame(data=table_tot, columns=("id","date_raw","date_complete","date_day","date_month","date_year","user_id","room_id","tool","origin","config_teacher","ip_address", "city", "country", "latitude", "longitude"))
table_tot_df.to_csv('table_tot.csv')  

#################################### Number uses per month ####################################

nb_uses = []
mycursor.execute("SELECT date_year, date_month, COUNT(id) FROM statslog GROUP BY date_month, date_year;")
for x in mycursor:
  nb_uses.append(x)
nb_uses_df = pd.DataFrame(data=nb_uses, columns=("Year","Month","Number of uses"))
# print(nb_uses_df)
# nb_uses_html = nb_uses_df.to_html(classes='table table-stripped')
 
# # write html to file
# text_file = open("uses/uses_per_months_tab.html", "w")
# text_file.write(nb_uses_html)
# text_file.close()

fig = go.Figure()
for year, group in nb_uses_df.groupby("Year"):
    fig.add_trace(go.Scatter(
      x=group["Month"], 
      y=group["Number of uses"], 
      name=year,
      hovertemplate="Year = %s<br>Month = %%{x}<br>Number of uses = %%{y}<extra></extra>"% year
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
  height=450,
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("uses/uses_per_months_graph.html")
nb_uses_df.to_csv('uses/uses_per_months_tab.csv')  

# mycursor.execute("SELECT * INTO OUTFILE '/tables/uses.csv' FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM statslog;")



#################################### Nb users per month ####################################

nb_users = []
mycursor.execute("SELECT COUNT(DISTINCT user_id), date_month, date_year FROM statslog GROUP BY date_month, date_year;")
for x in mycursor:
  nb_users.append(x)
nb_users_df = pd.DataFrame(data=nb_users, columns=("Number of users", "Month", "Year"))

nb_users_df.Month = pd.Categorical(nb_users_df.Month,categories=months_in_order,ordered=True)
nb_users_df = nb_users_df.sort_values('Month')

fig = go.Figure()
for year, group in nb_users_df.groupby("Year"):
    fig.add_trace(go.Scatter(
       x=group["Month"], 
       y=group["Number of users"], 
       name=year,
       hovertemplate="Year = %s<br>Month = %%{x}<br>Number of users = %%{y}<extra></extra>"% year
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
mycursor.execute("SELECT a.user_id, a.date_month, a.date_year FROM statslog AS a JOIN (SELECT user_id, min(id) AS id FROM statslog GROUP BY user_id) AS b ON a.user_id = b.user_id WHERE a.id = b.id;")
for x in mycursor:
  first_use.append(x)
first_use_df = pd.DataFrame(data=first_use, columns=("user_id", "Month", "Year"))

first_use_df = first_use_df.groupby(['Month','Year'])['user_id'].agg('count').reset_index()
first_use_df = first_use_df.rename(columns={"user_id": "New users"})
first_use_df.Month = pd.Categorical(first_use_df.Month,categories=months_in_order,ordered=True)
first_use_df = first_use_df.sort_values('Month')

fig = go.Figure()
for year, group in first_use_df.groupby("Year"):
    fig.add_trace(go.Bar(
       x=group["Month"], 
       y=group["New users"], 
       name=year,
       hovertemplate="Year = %s<br>Month = %%{x}<br>New users = %%{y}<extra></extra>"% year, 
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
  height=450,
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)

fig.write_html("users/new_users_per_months_graph.html") 
first_use_df.to_csv('users/new_users_per_months_tab.csv')  


#################################### Nb uses and users since the beginning group by origin ####################################

mycursor.execute("SELECT date_month, date_year  FROM statslog limit 1;")
for x in mycursor:
  first_date =  x

users_by_origin = []
mycursor.execute("SELECT origin, COUNT(DISTINCT user_id) FROM statslog GROUP BY origin;")
for x in mycursor:
  users_by_origin.append(x)
users_by_origin_df = pd.DataFrame(data=users_by_origin, columns=("Origin", "Number of users"))

uses_by_origin = []
mycursor.execute("SELECT origin, COUNT(id) FROM statslog GROUP BY origin;")
for x in mycursor:
  uses_by_origin.append(x)
uses_by_origin_df = pd.DataFrame(data=uses_by_origin, columns=("Origin", "Number of uses"))
by_origin_df = pd.merge(users_by_origin_df, uses_by_origin_df, how='right', on='Origin')

tot_distinct = ["Total distinct"]
mycursor.execute("SELECT COUNT(DISTINCT user_id) FROM statslog;")
for x in mycursor:
  tot_distinct.append(x[0])
mycursor.execute("SELECT COUNT(id) FROM statslog;")
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
mycursor.execute("SELECT origin, tool, COUNT(id) FROM statslog GROUP BY origin, tool ;")
for x in mycursor:
  tools_used.append(x)
tools_used_df = pd.DataFrame(data=tools_used, columns=("Origin","Tool","Number of uses"))

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
  title={
      'text':"Tools used since "+first_date[0] +" "+str(first_date[1]), 
      'font':{
      'size':30, 
      'color':'black'}
    },
  width=700, 
  font=dict(
      family="Source sans pro",
      size=18,
      color="Black"
  )
)
 
fig.write_html("uses/tools_used_tab.html")
tools_used_df.to_csv('uses/tools_used_tab.csv') 


#################################### Configuration used ####################################

config_used = []
mycursor.execute("SELECT config_teacher, COUNT(id) FROM statslog WHERE tool='teacher' GROUP BY config_teacher;")
for x in mycursor:
  config_used.append(x)
config_used_df = pd.DataFrame(data=config_used, columns=("Configuration","Number of uses"))
config_used_df = config_used_df.sort_values('Number of uses', ascending=False)

fig = go.Figure(data=[go.Table(
  header=dict(values=['Configuration', 'Number of uses'],
              line_color='black',
              fill_color='rgb(52,82,255)',
              align='center',
              font=dict(color='white'),
              height=30),
  cells=dict(values=[config_used_df['Configuration'],config_used_df['Number of uses'],],
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
  width=700, 
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
mycursor.execute("SELECT date_year, date_month, MAX(percentage_char), MAX(characters) FROM translatelog WHERE date_day<17 GROUP BY date_year, date_month;")
for x in mycursor:
  deepl_per_month.append(x)
deepl_per_month_df = pd.DataFrame(data=deepl_per_month, columns=("Year", "Month","Percentage", "Characters"))
deepl_per_month_df["Percentage"] = deepl_per_month_df["Percentage"]/100

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
for year, group in deepl_per_month_df.groupby("Year"):
    fig.add_trace(go.Scatter(
      x=group["Month"], 
      y=group["Percentage"], 
      name=year, 
      yaxis='y',
      customdata=deepl_per_month_df[["Characters"]],  
      hovertemplate="Year = %s<br>17th of %%{x}<br>Percentage used = %%{y}<br>Number of characters used = %%{customdata[0]}<extra></extra>"% year
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
    xaxis_title="17th of the month",
    title={
      'text':"Deepl Pro usage", 
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
mycursor.execute("SELECT DATE(date_complete), MAX(percentage_char), MAX(characters) FROM translatelog GROUP BY DATE(date_complete);")
for x in mycursor:
  deepl_per_day.append(x)

deepl_per_day_df = pd.DataFrame(data=deepl_per_day, columns=("Date","Percentage", "Characters"))
deepl_per_day_df["Percentage"] = deepl_per_day_df["Percentage"]/100

fig = go.Figure()

#Percentage
fig.add_trace(go.Scatter(
    x=deepl_per_day_df["Date"], 
    y=deepl_per_day_df["Percentage"], 
    name="Percentage", 
    yaxis="y", 
    # showlegend=False, 
    customdata=deepl_per_day_df[["Characters"]],  
    hovertemplate="Date = %{x}<br>Cumulative percentage = %{y}<br>Number of characters used = %{customdata[0]}<extra></extra>"
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
      'text':"Deepl Pro usage per day", 
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

first_use_df_month = first_use_df.sort_values('Year')
if first_use_df_month.iloc[-1]["Month"] == nb_uses_df.iloc[-1]["Month"]:
   new_users_value = first_use_df_month.iloc[-1]["New users"]
   previous_users_value = first_use_df_month.iloc[-2]["New users"]
else:
   new_users_value = 0
   previous_users_value = first_use_df_month.iloc[-1]["New users"]

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


fig.add_trace(go.Indicator(
    mode = "number",
    value = nb_uses_df.iloc[-1]["Number of uses"],
    title= {
       'text':'Uses',
       'font':{
         'size':40}
      },
    delta = {'reference': nb_uses_df.iloc[-2]["Number of uses"], 'relative': True, 'valueformat':'.0%'},
    domain = {'x': [0.4, 0.6], 'y': [0.2, 1]}))

fig.add_trace(go.Indicator(
    mode = "delta",
    value = nb_uses_df.iloc[-1]["Number of uses"],
    title= {
       'text':'Compared to the previous month',
       'font':{
         'size':20, 
         'color':'grey'}
      },
    delta = {
       'reference': nb_uses_df.iloc[-2]["Number of uses"], 
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
       'text':'Deepl Pro usage<br><span style="font-size:20;color:gray">Reset on the 17th</span><br>',
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
      'text':nb_uses_df.iloc[-1]["Month"] + " " + nb_uses_df.iloc[-1]["Year"], 
      'font':{
         'size':40, 
         'color':'black'}
      },
   width = 1600, 
   font = {'family': "Source sans pro"})

fig.write_image("indicators/indicator_month.png")


#################################### Indicators total ####################################

if (len(by_origin_df.loc[by_origin_df['Origin'] == 'VM master', 'Number of users']) == 0):
   value_master = 0
else:
   value_master = by_origin_df.loc[by_origin_df['Origin'] == 'VM master', 'Number of users'].iloc[0]

fig = go.Figure()

fig.add_trace(go.Indicator(
    mode = "number",
    value = value_master,
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
    value = by_origin_df.loc[by_origin_df['Origin'] == 'Total distinct', 'Number of uses'].iloc[0],
    title= {
       'text':'Uses',
       'font':{
         'size':40}
      },
    domain = {'x': [0.4, 0.6], 'y': [0, 1]}))

fig.update_layout(
   title= {
      'text':"Since "+first_date[0] +" "+str(first_date[1]), 
      'font':{
         'size':40, 
         'color':'black'}
      },
   width = 1600, 
   font = {'family': "Source sans pro"})

fig.write_image("indicators/indicator_tot.png")

#################################### Geolocalisation ####################################

ip_to_translate = []
ip_translated_df = pd.DataFrame(columns=['IP_address', 'City', 'Country', 'Latitude', 'Longitude'])
mycursor.execute("SELECT ip_address FROM statslog WHERE (ip_address IS NOT NULL and city IS NULL and latitude IS NULL) GROUP BY ip_address;")
for x in mycursor:
  ip_to_translate.append(x)
ip_to_translate_df = pd.DataFrame(data=ip_to_translate, columns=["IP_address"])

i = 0
for ind in ip_to_translate_df.index:
  ip = ip_to_translate_df['IP_address'][ind]
  res = DbIpCity.get(ip, api_key="free")
  ip_translated_df.loc[i,'IP_address'] = res.ip_address
  ip_translated_df.loc[i,'City'] = res.city
  ip_translated_df.loc[i,'Country'] = res.country
  if (res.latitude != None):
    ip_translated_df.loc[i,'Latitude'] = res.latitude
  if (res.latitude != None):
    ip_translated_df.loc[i,'Longitude'] = res.longitude
  i = i+1
  time.sleep(5)

for j in ip_translated_df.index:
  mycursor = mydb.cursor()
  sql = "INSERT INTO iplog (IP_address, city, country, latitude, longitude) VALUES (%s, %s, %s, %s, %s)"
  val = ip_translated_df.loc[j].values.flatten().tolist()
  mycursor.execute(sql, val)
  mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog a JOIN iplog b on a.ip_address = b.IP_address set a.city=b.city, a.country=b.country, a.latitude=b.latitude, a.longitude=b.longitude WHERE (a.city IS NULL and a.latitude IS NULL)")
mydb.commit()

#################################### City with no coordinates ####################################

geolocator = Nominatim(user_agent="MyApp")

city_to_coordinates = []
mycursor.execute("SELECT city, country, latitude, longitude FROM statslog WHERE (ip_address IS NOT NULL and latitude IS NULL and longitude IS NULL and city IS NOT NULL and country IS NOT NULL) GROUP BY city, country, latitude, longitude;")
for x in mycursor:
  city_to_coordinates.append(x)
city_to_coordinates_df = pd.DataFrame(data=city_to_coordinates, columns=["City", "Country", "Latitude", "Longitude"])

for ind in city_to_coordinates_df.index:
  city = city_to_coordinates_df['City'][ind]
  country = city_to_coordinates_df['Country'][ind]
  loc = geolocator.geocode(city+','+ country)
  city_to_coordinates_df.loc[ind,'Latitude'] = loc.latitude
  city_to_coordinates_df.loc[ind,'Longitude'] = loc.longitude
  time.sleep(5)

mycursor = mydb.cursor()
for index, row in city_to_coordinates_df.iterrows():
    sql = 'UPDATE iplog SET latitude = %s, longitude = %s where (city = %s and country= %s)'
    mycursor.execute(sql, (row['Latitude'], row['Longitude'], row['City'], row['Country'],))
mydb.commit()

mycursor = mydb.cursor()
mycursor.execute("UPDATE statslog a JOIN iplog b on a.ip_address = b.IP_address set a.latitude=b.latitude, a.longitude=b.longitude WHERE (a.latitude IS NULL and a.longitude IS NULL)")
mydb.commit()

#################################### Map users and uses ####################################

coordinates = []
mycursor.execute("SELECT city, latitude, longitude, COUNT(DISTINCT user_id), COUNT(id) FROM statslog GROUP BY latitude, longitude, city;")
for x in mycursor:
  coordinates.append(x)
coordinates_df = pd.DataFrame(data=coordinates, columns=["City", "Latitude", "Longitude", "Users", "Uses"])

fig = go.Figure()
fig = px.scatter_geo(coordinates_df,
                    lat="Latitude",
                    lon="Longitude",
                    size="Users",
                    hover_name="City")

fig.write_html("users/map_ipaddress.html")

fig = go.Figure()
fig = px.scatter_geo(coordinates_df,
                    lat="Latitude",
                    lon="Longitude",
                    size="Uses",
                    hover_name="City")

fig.write_html("uses/map_ipaddress.html")

coordinates_df.to_csv('users/map_ipaddress_tab.csv') 