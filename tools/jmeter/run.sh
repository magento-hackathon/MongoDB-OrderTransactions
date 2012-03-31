#!/bin/bash

# The host under test.
HOST=www.kookai.co.uk

# A Customer username.
USER='test@ibuildings.com'

# Customer's password
PASS='password'

RUNTIME=60
SLEEPTIME=0

# Ramp up the threadcount
for thread_count in 12 16 24 32 48 64 96 128 192 256 384 512 758 1024 1516 2048 3032
do
    echo "***************************************************************************"
    echo "Starting to melt your CPU with" $thread_count "users for" $RUNTIME "Seconds"
    echo "***************************************************************************"
    JVM_ARGS="-Xms512m -Xmx1024m" ./bin/jmeter.sh -n -t MagentoStress.jmx -Jhost=$HOST -Juser=$USER -Jpassword=$PASS -Jthreads=$thread_count -Jruntime=$RUNTIME
    echo "Your CPU made it through. Phew!" $HOST "should still be available?"
    echo "***************************************************************************"
    echo "Sleeping for" $SLEEPTIME "seconds, to let it cool down"
    echo "***************************************************************************"
    sleep $SLEEPTIME
done