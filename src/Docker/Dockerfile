FROM python:3.9-slim

# Create a restricted user to run the code
RUN useradd -ms /bin/bash restricted

# Set permissions so the restricted user can only access certain directories
RUN mkdir /home/restricted/code && chown restricted:restricted /home/restricted/code

# Set the working directory
WORKDIR /home/restricted/code

# Change to restricted user
USER restricted