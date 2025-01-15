from user_code import run_code

def main():
    with open('input.txt', 'r') as file:
        inputs = file.readlines()
        
    inputs = [input.strip() for input in inputs]
        
    # Unsafe due to the fact that the user can execute any code
    # TODO: Limit the user's code to a certain time and memory limit
    run_code(inputs)

if __name__ == '__main__':
    main()