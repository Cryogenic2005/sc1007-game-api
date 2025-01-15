FROM python:3.9-slim

# Create a restricted user to run the code
RUN useradd -ms /bin/bash restricted

# Set the working directory
WORKDIR /home/restricted

# Copy to the working directory
COPY . .

# Change the ownership of the working directory to the restricted user
RUN chown -R restricted:restricted /home/restricted

# Change to restricted user
USER restricted