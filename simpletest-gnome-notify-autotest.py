#!/usr/bin/env python  
#encoding: utf-8
# simpletest-gnome-notify v0.1.1
# Rafael Lima (http://rafael.adm.br) at Myfreecomm (http://myfreecomm.com.br)
# http://rafael.adm.br/simpletest-gnome-notify
# License: http://creativecommons.org/licenses/by/2.5/

# Dependencies:

# * pyinotify
#  sudo apt-get install python-pyinotify

import os, sys, re
from pyinotify import WatchManager, Notifier, ProcessEvent, EventsCodes  

wm = WatchManager()

def Monitor():  
    class close_event(ProcessEvent):  
        def process_IN_CLOSE(self, event):  
            f = event.name and os.path.join(event.path, event.name) or event.path  
            if re.compile('(.*)\.php$').search(f, 1):
                output = os.popen(SimpletestGnomeNotify).read()
                print output

    notifier = Notifier(wm, close_event())  
  
    try:  
        while 1:  
            notifier.process_events()  
            if notifier.check_events():  
                notifier.read_events()  
    except KeyboardInterrupt:  
        notifier.stop()  
        return  
  
if __name__ == '__main__':  
    try:  
        simpletest_path = sys.argv[1]
        SimpletestGnomeNotify = os.getcwd()+'/'+os.path.dirname(__file__)+"/simpletest-gnome-notify.php "+os.getcwd()+'/'+simpletest_path
        dirlist = sys.argv[2:]
        if not len(dirlist): raise Exception  
    except:  
        print 'use: %s [simpletest_path] [project_path]' % sys.argv[0]
        sys.exit(1)  

    for path in dirlist:
        os.chdir(path)
        print 'monitoring %s' % os.getcwd()
        wm.add_watch(os.getcwd(), EventsCodes.IN_CLOSE_WRITE, rec=True)  
        print os.popen(SimpletestGnomeNotify).read()

    Monitor()
