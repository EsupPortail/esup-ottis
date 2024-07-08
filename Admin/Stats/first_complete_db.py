#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""Remplissage de la base de données des statistiques avec les connexions antérieures à la création de la base de données.

    Ce scrip est à lancer une seule fois, lors de la création de la base de données. Cette base de données est ensuite mise à jour directement dans l'application OMIST

Usage : 
=======
    py first_complete_db.py 
"""

import matplotlib.pyplot as plt
import pandas as pd
import mysql.connector
import os

dirname = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

statslogs_tot_df = pd.DataFrame(columns=["stats_raw", "origin"])

#Open stats test
# file_test = open(dirname + '/tmp/StatsLogs.txt', 'r')
# for line in file_test:
#     statslogs_tot_df = statslogs_tot_df._append({"stats_raw":line, "origin" : "VM test"}, ignore_index=True)
# file_test.close()

# #Open stats master
file_master = open(dirname + '/tmp/StatsLogs.txt', 'r')
for line in file_master:
    statslogs_tot_df = statslogs_tot_df._append({"stats_raw":line, "origin" : "VM master"}, ignore_index=True)
file_master.close()

#Dataframe formatting
statslogs_tot_df[['date_raw', 'user_id', 'room_id']] = statslogs_tot_df['stats_raw'].str.split('|', expand=True)
statslogs_tot_df['date_complete'] = [x[:10] for x in statslogs_tot_df['date_raw']]
statslogs_tot_df['date_year'] = [x[:4] for x in statslogs_tot_df['date_raw']]
statslogs_tot_df['room_id'] = statslogs_tot_df['room_id'].str[:-1]

#Insertion into the database
mydb = mysql.connector.connect(
    user = "root",
    password = "root",
    host = "localhost",
    database = "db_stats")
mycursor = mydb.cursor()

for index, row in statslogs_tot_df.iterrows():
    sql_query = "INSERT INTO statslog (date_raw, date_complete, date_year, user_id, room_id, origin) VALUES (%s, %s, %s, %s, %s, %s);"
    val = (row['date_raw'], row['date_complete'], row['date_year'], row['user_id'], row['room_id'], row['origin'])
    mycursor.execute(sql_query, val)

sql_update = "UPDATE statslog SET date_month = DATE_FORMAT(date_complete, '%M');"
mycursor.execute(sql_update)

sql_update2 = "UPDATE statslog SET tool = 'teacher';"
mycursor.execute(sql_update2)

sql_update3 = "UPDATE statslog SET date_day = DATE_FORMAT(date_complete, '%d');"
mycursor.execute(sql_update3)

mydb.commit()
mydb.close()